<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelPolicyStoreRequest;
use App\Http\Requests\HotelPolicyUpdateRequest;
use App\Http\Resources\HotelPolicyCollection;
use App\Http\Resources\HotelPolicyResource;
use App\Models\HotelPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HotelPolicyController extends Controller
{
    public function index(Request $request): HotelPolicyCollection
    {
        $hotelPolicies = HotelPolicy::all();

        return new HotelPolicyCollection($hotelPolicies);
    }

    public function store(HotelPolicyStoreRequest $request): HotelPolicyResource
    {
        $hotelPolicy = HotelPolicy::create($request->validated());

        return new HotelPolicyResource($hotelPolicy);
    }

    public function show(Request $request, HotelPolicy $hotelPolicy): HotelPolicyResource
    {
        return new HotelPolicyResource($hotelPolicy);
    }

    public function update(HotelPolicyUpdateRequest $request, HotelPolicy $hotelPolicy): HotelPolicyResource
    {
        $hotelPolicy->update($request->validated());

        return new HotelPolicyResource($hotelPolicy);
    }

    public function destroy(Request $request, HotelPolicy $hotelPolicy): Response
    {
        $hotelPolicy->delete();

        return response()->noContent();
    }
}
