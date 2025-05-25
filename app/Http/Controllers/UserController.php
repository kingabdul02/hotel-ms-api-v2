<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use JsonResponse;

    public function index(Request $request): UserCollection
    {
        $users = User::all();

        return new UserCollection($users);
    }

    public function activateUser(Request $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return $this->error('User not found!');
        }

        $user->is_active = true;
        $user->save();

        return $this->success('User activated successfully!');
    }

    public function deactivateUser(Request $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return $this->error('User not found!');
        }

        $user->is_active = false;
        $user->save();

        return $this->success('User deactivated successfully!');
    }
}
