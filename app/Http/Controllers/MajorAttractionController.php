<?php

namespace App\Http\Controllers;

use App\Http\Requests\MajorAttractionStoreRequest;
use App\Http\Requests\MajorAttractionUpdateRequest;
use App\Http\Resources\MajorAttractionCollection;
use App\Http\Resources\MajorAttractionResource;
use App\Models\MajorAttraction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MajorAttractionController extends Controller
{
    public function index(Request $request): MajorAttractionCollection
    {
        $majorAttractions = MajorAttraction::all();

        return new MajorAttractionCollection($majorAttractions);
    }

    public function store(MajorAttractionStoreRequest $request): MajorAttractionResource
    {
        $majorAttraction = MajorAttraction::create($request->validated());

        return new MajorAttractionResource($majorAttraction);
    }

    public function show(Request $request, MajorAttraction $majorAttraction): MajorAttractionResource
    {
        return new MajorAttractionResource($majorAttraction);
    }

    public function update(MajorAttractionUpdateRequest $request, MajorAttraction $majorAttraction): MajorAttractionResource
    {
        $majorAttraction->update($request->validated());

        return new MajorAttractionResource($majorAttraction);
    }

    public function destroy(Request $request, MajorAttraction $majorAttraction): Response
    {
        $majorAttraction->delete();

        return response()->noContent();
    }
}
