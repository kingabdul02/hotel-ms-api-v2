<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserEmailVerificationStoreRequest;
use App\Http\Requests\UserEmailVerificationUpdateRequest;
use App\Http\Resources\UserEmailVerificationCollection;
use App\Http\Resources\UserEmailVerificationResource;
use App\Models\UserEmailVerification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserEmailVerificationController extends Controller
{
    public function index(Request $request): UserEmailVerificationCollection
    {
        $userEmailVerifications = UserEmailVerification::all();

        return new UserEmailVerificationCollection($userEmailVerifications);
    }

    public function store(UserEmailVerificationStoreRequest $request): UserEmailVerificationResource
    {
        $userEmailVerification = UserEmailVerification::create($request->validated());

        return new UserEmailVerificationResource($userEmailVerification);
    }

    public function show(Request $request, UserEmailVerification $userEmailVerification): UserEmailVerificationResource
    {
        return new UserEmailVerificationResource($userEmailVerification);
    }

    public function update(UserEmailVerificationUpdateRequest $request, UserEmailVerification $userEmailVerification): UserEmailVerificationResource
    {
        $userEmailVerification->update($request->validated());

        return new UserEmailVerificationResource($userEmailVerification);
    }

    public function destroy(Request $request, UserEmailVerification $userEmailVerification): Response
    {
        $userEmailVerification->delete();

        return response()->noContent();
    }
}
