<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\GeneralSettingsController
 */
final class GeneralSettingsControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_behaves_as_expected(): void
    {
        $generalSettings = GeneralSettings::factory()->count(3)->create();

        $response = $this->get(route('general-settings.index'));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\GeneralSettingsController::class,
            'store',
            \App\Http\Requests\GeneralSettingsStoreRequest::class
        );
    }

    #[Test]
    public function store_saves(): void
    {
        $response = $this->post(route('general-settings.store'));

        $response->assertCreated();
        $response->assertJsonStructure([]);

        $this->assertDatabaseHas(generalSettings, [ /* ... */ ]);
    }


    #[Test]
    public function show_behaves_as_expected(): void
    {
        $generalSetting = GeneralSettings::factory()->create();

        $response = $this->get(route('general-settings.show', $generalSetting));

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\GeneralSettingsController::class,
            'update',
            \App\Http\Requests\GeneralSettingsUpdateRequest::class
        );
    }

    #[Test]
    public function update_behaves_as_expected(): void
    {
        $generalSetting = GeneralSettings::factory()->create();

        $response = $this->put(route('general-settings.update', $generalSetting));

        $generalSetting->refresh();

        $response->assertOk();
        $response->assertJsonStructure([]);
    }


    #[Test]
    public function destroy_deletes_and_responds_with(): void
    {
        $generalSetting = GeneralSettings::factory()->create();
        $generalSetting = GeneralSetting::factory()->create();

        $response = $this->delete(route('general-settings.destroy', $generalSetting));

        $response->assertNoContent();

        $this->assertSoftDeleted($generalSetting);
    }
}
