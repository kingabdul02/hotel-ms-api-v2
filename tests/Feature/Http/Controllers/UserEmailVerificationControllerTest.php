<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\UserEmailVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\UserEmailVerificationController
 */
final class UserEmailVerificationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $userEmailVerifications = UserEmailVerification::factory()->count(3)->create();

        $response = $this->get(route('user-email-verifications.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\UserEmailVerificationController::class,
            'store',
            \App\Http\Requests\UserEmailVerificationStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $email = $this->faker->safeEmail();
        $verification_token = $this->faker->word();

        $response = $this->post(route('user-email-verifications.store'), [
            'email' => $email,
            'verification_token' => $verification_token,
        ]);

        $userEmailVerifications = UserEmailVerification::query()
            ->where('email', $email)
            ->where('verification_token', $verification_token)
            ->get();
        $this->assertCount(1, $userEmailVerifications);
        $userEmailVerification = $userEmailVerifications->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $userEmailVerification = UserEmailVerification::factory()->create();

        $response = $this->get(route('user-email-verifications.show', $userEmailVerification));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\UserEmailVerificationController::class,
            'update',
            \App\Http\Requests\UserEmailVerificationUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $userEmailVerification = UserEmailVerification::factory()->create();
        $email = $this->faker->safeEmail();
        $verification_token = $this->faker->word();

        $response = $this->put(route('user-email-verifications.update', $userEmailVerification), [
            'email' => $email,
            'verification_token' => $verification_token,
        ]);

        $userEmailVerification->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($email, $userEmailVerification->email);
        $this->assertEquals($verification_token, $userEmailVerification->verification_token);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $userEmailVerification = UserEmailVerification::factory()->create();

        $response = $this->delete(route('user-email-verifications.destroy', $userEmailVerification));

        $response->assertNoContent();

        $this->assertModelMissing($userEmailVerification);
    }
}
