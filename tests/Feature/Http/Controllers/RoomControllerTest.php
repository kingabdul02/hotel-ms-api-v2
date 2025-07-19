<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\RoomController
 */
final class RoomControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $rooms = Room::factory()->count(3)->create();

        $response = $this->get(route('rooms.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\RoomController::class,
            'store',
            \App\Http\Requests\RoomStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = $this->faker->name();
        $room_type = RoomType::factory()->create();
        $hotel = Hotel::factory()->create();
        $price = $this->faker->randomFloat(/** float_attributes **/);
        $is_available = $this->faker->boolean();
        $image = $this->faker->word();
        $no_of_guests = $this->faker->word();
        $no_of_bedrooms = $this->faker->word();
        $no_of_beds = $this->faker->word();
        $no_of_baths = $this->faker->word();
        $is_feature = $this->faker->boolean();

        $response = $this->post(route('rooms.store'), [
            'name' => $name,
            'room_type_id' => $room_type->id,
            'hotel_id' => $hotel->id,
            'price' => $price,
            'is_available' => $is_available,
            'image' => $image,
            'no_of_guests' => $no_of_guests,
            'no_of_bedrooms' => $no_of_bedrooms,
            'no_of_beds' => $no_of_beds,
            'no_of_baths' => $no_of_baths,
            'is_feature' => $is_feature,
        ]);

        $rooms = Room::query()
            ->where('name', $name)
            ->where('room_type_id', $room_type->id)
            ->where('hotel_id', $hotel->id)
            ->where('price', $price)
            ->where('is_available', $is_available)
            ->where('image', $image)
            ->where('no_of_guests', $no_of_guests)
            ->where('no_of_bedrooms', $no_of_bedrooms)
            ->where('no_of_beds', $no_of_beds)
            ->where('no_of_baths', $no_of_baths)
            ->where('is_feature', $is_feature)
            ->get();
        $this->assertCount(1, $rooms);
        $room = $rooms->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $room = Room::factory()->create();

        $response = $this->get(route('rooms.show', $room));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\RoomController::class,
            'update',
            \App\Http\Requests\RoomUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $room = Room::factory()->create();
        $name = $this->faker->name();
        $room_type = RoomType::factory()->create();
        $hotel = Hotel::factory()->create();
        $price = $this->faker->randomFloat(/** float_attributes **/);
        $is_available = $this->faker->boolean();
        $image = $this->faker->word();
        $no_of_guests = $this->faker->word();
        $no_of_bedrooms = $this->faker->word();
        $no_of_beds = $this->faker->word();
        $no_of_baths = $this->faker->word();
        $is_feature = $this->faker->boolean();

        $response = $this->put(route('rooms.update', $room), [
            'name' => $name,
            'room_type_id' => $room_type->id,
            'hotel_id' => $hotel->id,
            'price' => $price,
            'is_available' => $is_available,
            'image' => $image,
            'no_of_guests' => $no_of_guests,
            'no_of_bedrooms' => $no_of_bedrooms,
            'no_of_beds' => $no_of_beds,
            'no_of_baths' => $no_of_baths,
            'is_feature' => $is_feature,
        ]);

        $room->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $room->name);
        $this->assertEquals($room_type->id, $room->room_type_id);
        $this->assertEquals($hotel->id, $room->hotel_id);
        $this->assertEquals($price, $room->price);
        $this->assertEquals($is_available, $room->is_available);
        $this->assertEquals($image, $room->image);
        $this->assertEquals($no_of_guests, $room->no_of_guests);
        $this->assertEquals($no_of_bedrooms, $room->no_of_bedrooms);
        $this->assertEquals($no_of_beds, $room->no_of_beds);
        $this->assertEquals($no_of_baths, $room->no_of_baths);
        $this->assertEquals($is_feature, $room->is_feature);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $room = Room::factory()->create();

        $response = $this->delete(route('rooms.destroy', $room));

        $response->assertNoContent();

        $this->assertSoftDeleted($room);
    }
}
