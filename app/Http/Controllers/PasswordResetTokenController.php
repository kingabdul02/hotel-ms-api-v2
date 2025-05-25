<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordResetTokenStoreRequest;
use App\Http\Requests\PasswordResetTokenUpdateRequest;
use App\Http\Resources\PasswordResetTokenCollection;
use App\Http\Resources\PasswordResetTokenResource;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PasswordResetTokenController extends Controller
{
    public function index(Request $request): PasswordResetTokenCollection
    {
        $passwordResetTokens = PasswordResetToken::all();

        return new PasswordResetTokenCollection($passwordResetTokens);
    }

    public function store(PasswordResetTokenStoreRequest $request): PasswordResetTokenResource
    {
        $passwordResetToken = PasswordResetToken::create($request->validated());

        return new PasswordResetTokenResource($passwordResetToken);
    }

    public function show(Request $request, PasswordResetToken $passwordResetToken): PasswordResetTokenResource
    {
        return new PasswordResetTokenResource($passwordResetToken);
    }

    public function update(PasswordResetTokenUpdateRequest $request, PasswordResetToken $passwordResetToken): PasswordResetTokenResource
    {
        $passwordResetToken->update($request->validated());

        return new PasswordResetTokenResource($passwordResetToken);
    }

    public function destroy(Request $request, PasswordResetToken $passwordResetToken): Response
    {
        $passwordResetToken->delete();

        return response()->noContent();
    }
}
