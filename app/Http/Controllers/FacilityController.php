<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacilityStoreRequest;
use App\Http\Requests\FacilityUpdateRequest;
use App\Http\Resources\FacilityCollection;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FacilityController extends Controller
{
    public function index(Request $request): FacilityCollection
    {
        $facilities = Facility::all();

        return new FacilityCollection($facilities);
    }

    public function store(FacilityStoreRequest $request): FacilityResource
    {
        $facility = Facility::create($request->validated());

        return new FacilityResource($facility);
    }

    public function show(Request $request, Facility $facility): FacilityResource
    {
        return new FacilityResource($facility);
    }

    public function update(FacilityUpdateRequest $request, Facility $facility): FacilityResource
    {
        $facility->update($request->validated());

        return new FacilityResource($facility);
    }

    public function destroy(Request $request, Facility $facility): Response
    {
        $facility->delete();

        return response()->noContent();
    }
}
