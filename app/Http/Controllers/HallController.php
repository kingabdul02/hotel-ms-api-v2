<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use Illuminate\Http\Request;
use App\Http\Resources\HallResource;
use App\Http\Requests\StoreHallRequest;
use App\Http\Requests\UpdateHallRequest;
use App\Services\FileUploadService;
use App\Traits\JsonResponse;

class HallController extends Controller
{
    use JsonResponse;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $halls = Hall::all();
        return $this->success(HallResource::collection($halls), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHallRequest $request)
    {
        $validated = $request->validated();

        $hall = Hall::create($validated);
        return $this->success(new HallResource($hall), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Hall $hall)
    {
        return $this->success(new HallResource($hall), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHallRequest $request, Hall $hall)
    {
        $validated = $request->validated();

        $hall->update($validated);
        return $this->success('Hall updated successfully', 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hall $hall)
    {
        $hall->delete();
        return $this->success('Hall deleted successfully', 204);
    }
}
