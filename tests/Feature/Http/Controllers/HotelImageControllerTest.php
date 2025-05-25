<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Hotel;
use App\Models\HotelImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\HotelImageController
 */
final class HotelImageControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $hotelImages = HotelImage::factory()->count(3)->create();

        $response = $this->get(route('hotel-images.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\HotelImageController::class,
            'store',
            \App\Http\Requests\HotelImageStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $url = $this->faker->url();
        $hotel = Hotel::factory()->create();

        $response = $this->post(route('hotel-images.store'), [
            'url' => $url,
            'hotel_id' => $hotel->id,
        ]);

        $hotelImages = HotelImage::query()
            ->where('url', $url)
            ->where('hotel_id', $hotel->id)
            ->get();
        $this->assertCount(1, $hotelImages);
        $hotelImage = $hotelImages->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $hotelImage = HotelImage::factory()->create();

        $response = $this->get(route('hotel-images.show', $hotelImage));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\HotelImageController::class,
            'update',
            \App\Http\Requests\HotelImageUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $hotelImage = HotelImage::factory()->create();
        $url = $this->faker->url();
        $hotel = Hotel::factory()->create();

        $response = $this->put(route('hotel-images.update', $hotelImage), [
            'url' => $url,
            'hotel_id' => $hotel->id,
        ]);

        $hotelImage->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($url, $hotelImage->url);
        $this->assertEquals($hotel->id, $hotelImage->hotel_id);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $hotelImage = HotelImage::factory()->create();

        $response = $this->delete(route('hotel-images.destroy', $hotelImage));

        $response->assertNoContent();

        $this->assertSoftDeleted($hotelImage);
    }
}
