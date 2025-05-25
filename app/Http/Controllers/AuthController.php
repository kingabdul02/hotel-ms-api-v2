<?php

namespace App\Http\Controllers;


use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Models\UserEmailVerification;
use App\Notifications\LoginSuccess;
use App\Notifications\PasswordReset;
use App\Notifications\ResendVerification;
use App\Notifications\SuccessPasswordChange;
use App\Notifications\SuccessRegistration;
use App\Services\FileUploadService;
use App\Traits\JsonResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use JsonResponse;

    public function __construct(
        protected FileUploadService $uploadService,
    ){}
    /**
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        if (auth()->attempt($request->only('email', 'password'))) {
            $user = auth()->user();
            if ($user->is_active) {
                try {
                    $user->notify(new LoginSuccess($user));
                } catch (Exception $e) {
                    Log::alert($e);
                }

                return $this->success(
                    [
                        'access_token' => $user->createToken('access_token')->plainTextToken,
                        'token_type' => 'Bearer',
                        'user' => new UserResource($user),
                    ],
                    200
                );
            }else {
                auth()->user()->tokens()->delete();
                return $this->error('User is inactive or not verified', 401);
            }
        }

        return $this->error('The provided credentials are incorrect.', 401);
    }

    /**
     * @unauthenticated
     */
    public function register(RegistrationRequest $request)
    {
        return DB::transaction(function () use($request) {
            $user = User::create($request->validated())->assignRole($request['role']);

            try {
                $user->notify((new SuccessRegistration()));
            } catch (Exception $e) {
                    Log::alert($e);
                }

            return $this->success(['user' => $user], 201);
        });
    }

    /**
     * @unauthenticated
     */
    public function uploadFile(Request $request)
    {
        try {
            $uploadedData = $this->uploadService->upload('public', 'uploads', $request->file('file'), [
                'max:2048', // Maximum file size in kilobytes
                'mimes:image/jpeg,image/png,image/gif', // Allowed MIME types
            ]);

            // Handle successful upload with $uploadedData
            return $uploadedData;
        } catch (ValidationException $e) {
            // Handle validation errors
        } catch (\RuntimeException $e) {
            // Handle upload failure
        }
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json(null, 200);
    }

     /**
     * @unauthenticated
     */
    public function resetPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->success([
                'email_exist' => false
            ]);
        }

        $token_exit = PasswordResetToken::where('email', $request->email);

        if ($token_exit) {
            $token_exit->delete();
        }

        $token = new PasswordResetToken();
        $token->email = $user->email;
        $token->token = uniqid('res_');
        $token->save();

        try {
            $user->notify(new PasswordReset($user, $token->token));
        } catch (Exception $e) {
            Log::alert($e);
        }

        return $this->success([
            "email_exist" => true
        ]);
    }

    /**
     * @unauthenticated
     */
    public function verifyResetToken(Request $request)
    {
        $token = $request->token;
        $resetTokenRecord = PasswordResetToken::where('token', $token)->first();

        if (!$resetTokenRecord) {
            return $this->error('Invalid token', 404);
        }

        $token = $resetTokenRecord->token;
        return redirect(env('CLIENT_URL') . '/auth/forgot-password/update/' . $token)->with('success', 'Password verified successfully');
    }

    /**
     * @unauthenticated
     */
    public function updatePassword(Request $request)
    {
        $token = $request->token;
        $token = PasswordResetToken::where('token', $token)->first();
        if ($token) {
            $user = User::where('email', $token->email)->first();

            $user->password = bcrypt($request->password);
            $user->save();

            $token->delete();

            try {
                $user->notify(new SuccessPasswordChange());
            } catch (Exception $e) {
                Log::alert($e);
            }

            return $this->success([
                'message' => 'Password was changed successfully!',
            ]);
        } else {
            return $this->error('Invalid token', 404);
        }
    }

    /**
     * @unauthenticated
     */
    public function verifyEmail(Request $request)
    {
        $token = $request->token;
        $user = UserEmailVerification::where('verification_token', $token)->first();

        if (!$user) {
            return redirect(env('PORTAL_URL') . '/auth/verification-failed');
        }

        $verified_user = User::where('email', $user->email)->first();
        $verified_user->email_verified = true;
        $verified_user->email_verified_at = Carbon::now();
        $verified_user->is_active = true;
        $verified_user->save();

        $user->delete();

        return redirect(env('PORTAL_URL') . '/auth/success-verification');
    }

    /**
     * @unauthenticated
     */
    public function resendVeriificationMail(Request $request)
    {
        $newToken = $this->generateToken();

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('Something went wrong!', 400);
        }

        $userVerification = UserEmailVerification::where('eamil', $request->email)->first();

        if (!$userVerification) {
            return $this->success('Email has already been verified', 400);
        }

        $userVerification->verificationToken = $newToken;
        $userVerification->save();

        try {
            $user->notify(new ResendVerification($user, $newToken));
        } catch (Exception $e) {
            Log::alert($e);
        }

        return $this->success('Email verification sent', 200);
    }

    public function generateToken()
    {
        return uniqid('tkn_');
    }

    public function getMyProfile(Request $request) : UserResource {
        $user = auth()->user();

        return new UserResource($user->load('bookings', 'savedBookings', 'notifications'));
    }

    public function updateProfile(UpdateProfileRequest $request) {
        if (!$request->anyFilled(['phone', 'password', 'address', 'profile_url'])) {
            return false;
        }

        $user = auth()->user();

        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->save();

        return new UserResource($user->load('bookings', 'savedBookings', 'notifications'));
    }
}
