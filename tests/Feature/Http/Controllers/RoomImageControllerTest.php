<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\RoomImageController
 */
final class RoomImageControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $roomImages = RoomImage::factory()->count(3)->create();

        $response = $this->get(route('room-images.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\RoomImageController::class,
            'store',
            \App\Http\Requests\RoomImageStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $url = $this->faker->url();
        $room = Room::factory()->create();

        $response = $this->post(route('room-images.store'), [
            'url' => $url,
            'room_id' => $room->id,
        ]);

        $roomImages = RoomImage::query()
            ->where('url', $url)
            ->where('room_id', $room->id)
            ->get();
        $this->assertCount(1, $roomImages);
        $roomImage = $roomImages->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $roomImage = RoomImage::factory()->create();

        $response = $this->get(route('room-images.show', $roomImage));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\RoomImageController::class,
            'update',
            \App\Http\Requests\RoomImageUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $roomImage = RoomImage::factory()->create();
        $url = $this->faker->url();
        $room = Room::factory()->create();

        $response = $this->put(route('room-images.update', $roomImage), [
            'url' => $url,
            'room_id' => $room->id,
        ]);

        $roomImage->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($url, $roomImage->url);
        $this->assertEquals($room->id, $roomImage->room_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $roomImage = RoomImage::factory()->create();

        $response = $this->delete(route('room-images.destroy', $roomImage));

        $response->assertNoContent();

        $this->assertSoftDeleted($roomImage);
    }
}
