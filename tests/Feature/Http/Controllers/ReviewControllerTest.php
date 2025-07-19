<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Hotel;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ReviewController
 */
final class ReviewControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $reviews = Review::factory()->count(3)->create();

        $response = $this->get(route('reviews.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ReviewController::class,
            'store',
            \App\Http\Requests\ReviewStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $hotel = Hotel::factory()->create();
        $user = User::factory()->create();
        $rating = $this->faker->word();
        $comment = $this->faker->text();

        $response = $this->post(route('reviews.store'), [
            'hotel_id' => $hotel->id,
            'user_id' => $user->id,
            'rating' => $rating,
            'comment' => $comment,
        ]);

        $reviews = Review::query()
            ->where('hotel_id', $hotel->id)
            ->where('user_id', $user->id)
            ->where('rating', $rating)
            ->where('comment', $comment)
            ->get();
        $this->assertCount(1, $reviews);
        $review = $reviews->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $review = Review::factory()->create();

        $response = $this->get(route('reviews.show', $review));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ReviewController::class,
            'update',
            \App\Http\Requests\ReviewUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $review = Review::factory()->create();
        $hotel = Hotel::factory()->create();
        $user = User::factory()->create();
        $rating = $this->faker->word();
        $comment = $this->faker->text();

        $response = $this->put(route('reviews.update', $review), [
            'hotel_id' => $hotel->id,
            'user_id' => $user->id,
            'rating' => $rating,
            'comment' => $comment,
        ]);

        $review->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($hotel->id, $review->hotel_id);
        $this->assertEquals($user->id, $review->user_id);
        $this->assertEquals($rating, $review->rating);
        $this->assertEquals($comment, $review->comment);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $review = Review::factory()->create();

        $response = $this->delete(route('reviews.destroy', $review));

        $response->assertNoContent();

        $this->assertSoftDeleted($review);
    }
}
