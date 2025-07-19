<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ItemController
 */
final class ItemControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $items = Item::factory()->count(3)->create();

        $response = $this->get(route('items.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ItemController::class,
            'store',
            \App\Http\Requests\ItemStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $name = $this->faker->name();
        $category = Category::factory()->create();
        $unit_price = $this->faker->randomFloat(/** float_attributes **/);
        $reorder_level = $this->faker->word();

        $response = $this->post(route('items.store'), [
            'name' => $name,
            'category_id' => $category->id,
            'unit_price' => $unit_price,
            'reorder_level' => $reorder_level,
        ]);

        $items = Item::query()
            ->where('name', $name)
            ->where('category_id', $category->id)
            ->where('unit_price', $unit_price)
            ->where('reorder_level', $reorder_level)
            ->get();
        $this->assertCount(1, $items);
        $item = $items->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $item = Item::factory()->create();

        $response = $this->get(route('items.show', $item));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ItemController::class,
            'update',
            \App\Http\Requests\ItemUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $item = Item::factory()->create();
        $name = $this->faker->name();
        $category = Category::factory()->create();
        $unit_price = $this->faker->randomFloat(/** float_attributes **/);
        $reorder_level = $this->faker->word();

        $response = $this->put(route('items.update', $item), [
            'name' => $name,
            'category_id' => $category->id,
            'unit_price' => $unit_price,
            'reorder_level' => $reorder_level,
        ]);

        $item->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($name, $item->name);
        $this->assertEquals($category->id, $item->category_id);
        $this->assertEquals($unit_price, $item->unit_price);
        $this->assertEquals($reorder_level, $item->reorder_level);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $item = Item::factory()->create();

        $response = $this->delete(route('items.destroy', $item));

        $response->assertNoContent();

        $this->assertSoftDeleted($item);
    }
}
