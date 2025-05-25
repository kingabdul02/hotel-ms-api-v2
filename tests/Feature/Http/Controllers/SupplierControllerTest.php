<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\SupplierController
 */
final class SupplierControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $suppliers = Supplier::factory()->count(3)->create();

        $response = $this->get(route('suppliers.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SupplierController::class,
            'store',
            \App\Http\Requests\SupplierStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $supplier_name = $this->faker->word();
        $address = $this->faker->text();
        $phone = $this->faker->phoneNumber();
        $email = $this->faker->safeEmail();

        $response = $this->post(route('suppliers.store'), [
            'supplier_name' => $supplier_name,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
        ]);

        $suppliers = Supplier::query()
            ->where('supplier_name', $supplier_name)
            ->where('address', $address)
            ->where('phone', $phone)
            ->where('email', $email)
            ->get();
        $this->assertCount(1, $suppliers);
        $supplier = $suppliers->first();

        $response->assertCreated();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->get(route('suppliers.show', $supplier));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\SupplierController::class,
            'update',
            \App\Http\Requests\SupplierUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $supplier = Supplier::factory()->create();
        $supplier_name = $this->faker->word();
        $address = $this->faker->text();
        $phone = $this->faker->phoneNumber();
        $email = $this->faker->safeEmail();

        $response = $this->put(route('suppliers.update', $supplier), [
            'supplier_name' => $supplier_name,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
        ]);

        $supplier->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertEquals($supplier_name, $supplier->supplier_name);
        $this->assertEquals($address, $supplier->address);
        $this->assertEquals($phone, $supplier->phone);
        $this->assertEquals($email, $supplier->email);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->delete(route('suppliers.destroy', $supplier));

        $response->assertNoContent();

        $this->assertSoftDeleted($supplier);
    }
}
