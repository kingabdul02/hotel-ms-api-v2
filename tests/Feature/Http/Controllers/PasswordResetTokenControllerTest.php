<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\PasswordResetToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\PasswordResetTokenController
 */
final class PasswordResetTokenControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $passwordResetTokens = PasswordResetToken::factory()->count(3)->create();

        $response = $this->get(route('password-reset-tokens.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PasswordResetTokenController::class,
            'store',
            \App\Http\Requests\PasswordResetTokenStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $email = $this->faker->safeEmail();
        $token = $this->faker->word();

        $response = $this->post(route('password-reset-tokens.store'), [
            'email' => $email,
            'token' => $token,
        ]);

        $passwordResetTokens = PasswordResetToken::query()
            ->where('email', $email)
            ->where('token', $token)
            ->get();
        $this->assertCount(1, $passwordResetTokens);
        $passwordResetToken = $passwordResetTokens->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $passwordResetToken = PasswordResetToken::factory()->create();

        $response = $this->get(route('password-reset-tokens.show', $passwordResetToken));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\PasswordResetTokenController::class,
            'update',
            \App\Http\Requests\PasswordResetTokenUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $passwordResetToken = PasswordResetToken::factory()->create();
        $email = $this->faker->safeEmail();
        $token = $this->faker->word();

        $response = $this->put(route('password-reset-tokens.update', $passwordResetToken), [
            'email' => $email,
            'token' => $token,
        ]);

        $passwordResetToken->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($email, $passwordResetToken->email);
        $this->assertEquals($token, $passwordResetToken->token);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $passwordResetToken = PasswordResetToken::factory()->create();

        $response = $this->delete(route('password-reset-tokens.destroy', $passwordResetToken));

        $response->assertNoContent();

        $this->assertModelMissing($passwordResetToken);
    }
}
