<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneralSettingsStoreRequest;
use App\Http\Requests\GeneralSettingsUpdateRequest;
use App\Http\Resources\GeneralSettingCollection;
use App\Http\Resources\GeneralSettingResource;
use App\Models\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GeneralSettingsController extends Controller
{
    public function index(Request $request): GeneralSettingCollection
    {
        $generalSettings = GeneralSetting::all();

        return new GeneralSettingCollection($generalSettings);
    }

    public function store(GeneralSettingsStoreRequest $request): GeneralSettingResource
    {
        $generalSetting = GeneralSetting::create($request->validated());

        return new GeneralSettingResource($generalSetting);
    }

    public function show(Request $request, GeneralSetting $generalSetting): GeneralSettingResource
    {
        return new GeneralSettingResource($generalSetting);
    }

    public function update(GeneralSettingsUpdateRequest $request, GeneralSetting $generalSetting): GeneralSettingResource
    {
        $generalSetting->update($request->validated());

        return new GeneralSettingResource($generalSetting);
    }

    public function destroy(Request $request, GeneralSetting $generalSetting): Response
    {
        $generalSetting->delete();

        return response()->noContent();
    }
}
