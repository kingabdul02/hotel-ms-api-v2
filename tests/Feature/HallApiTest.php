<?php

use App\Models\Hall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can retrieve a list of halls', function () {
    Hall::factory()->count(3)->create();

    $response = $this->getJson('/api/halls');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'message',
            'data' => [
                '*' => ['id', 'name', 'capacity', 'price', 'image']
            ]
        ]);
});

it('can create a new hall', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('hall.jpg');

    $hallData = [
        'name' => 'Grand Ballroom',
        'capacity' => 200,
        'price' => 1500.00,
        'image' => $file,
    ];

    $response = $this->postJson('/api/halls', $hallData);

    $response->assertCreated()
        ->assertJson(['message' => 'Hall created successfully'])
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'name', 'capacity', 'price', 'image']
        ]);

    $this->assertDatabaseHas('halls', [
        'name' => 'Grand Ballroom',
        'capacity' => 200,
        'price' => 1500.00,
    ]);

    Storage::disk('public')->assertExists(str_replace('/storage', '', $response['data']['image']));
});

it('can retrieve a single hall', function () {
    $hall = Hall::factory()->create();

    $response = $this->getJson("/api/halls/{$hall->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Hall retrieved successfully'])
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'name', 'capacity', 'price', 'image']
        ]);
});

it('can update an existing hall', function () {
    Storage::fake('public');

    $hall = Hall::factory()->create();
    $newFile = UploadedFile::fake()->image('new_hall.png');

    $updatedData = [
        'name' => 'Updated Ballroom',
        'capacity' => 250,
        'price' => 1800.00,
        'image' => $newFile,
    ];

    $response = $this->putJson("/api/halls/{$hall->id}", $updatedData);

    $response->assertOk()
        ->assertJson(['message' => 'Hall updated successfully'])
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'name', 'capacity', 'price', 'image']
        ]);

    $this->assertDatabaseHas('halls', [
        'id' => $hall->id,
        'name' => 'Updated Ballroom',
        'capacity' => 250,
        'price' => 1800.00,
    ]);

    Storage::disk('public')->assertExists(str_replace('/storage', '', $response['data']['image']));
    Storage::disk('public')->assertMissing(str_replace('/storage', '', $hall->image));
});

it('can delete a hall', function () {
    Storage::fake('public');

    $hall = Hall::factory()->create();

    $response = $this->deleteJson("/api/halls/{$hall->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('halls', ['id' => $hall->id]);
    Storage::disk('public')->assertMissing(str_replace('/storage', '', $hall->image));
});
