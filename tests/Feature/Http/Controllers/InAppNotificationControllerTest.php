<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\InAppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\InAppNotificationController
 */
final class InAppNotificationControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $inAppNotifications = InAppNotification::factory()->count(3)->create();

        $response = $this->get(route('in-app-notifications.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\InAppNotificationController::class,
            'store',
            \App\Http\Requests\InAppNotificationStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $user = User::factory()->create();
        $subject = $this->faker->word();
        $content = $this->faker->paragraphs(3, true);

        $response = $this->post(route('in-app-notifications.store'), [
            'user_id' => $user->id,
            'subject' => $subject,
            'content' => $content,
        ]);

        $inAppNotifications = InAppNotification::query()
            ->where('user_id', $user->id)
            ->where('subject', $subject)
            ->where('content', $content)
            ->get();
        $this->assertCount(1, $inAppNotifications);
        $inAppNotification = $inAppNotifications->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $inAppNotification = InAppNotification::factory()->create();

        $response = $this->get(route('in-app-notifications.show', $inAppNotification));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\InAppNotificationController::class,
            'update',
            \App\Http\Requests\InAppNotificationUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $inAppNotification = InAppNotification::factory()->create();
        $user = User::factory()->create();
        $subject = $this->faker->word();
        $content = $this->faker->paragraphs(3, true);

        $response = $this->put(route('in-app-notifications.update', $inAppNotification), [
            'user_id' => $user->id,
            'subject' => $subject,
            'content' => $content,
        ]);

        $inAppNotification->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($user->id, $inAppNotification->user_id);
        $this->assertEquals($subject, $inAppNotification->subject);
        $this->assertEquals($content, $inAppNotification->content);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $inAppNotification = InAppNotification::factory()->create();

        $response = $this->delete(route('in-app-notifications.destroy', $inAppNotification));

        $response->assertNoContent();

        $this->assertSoftDeleted($inAppNotification);
    }
}
