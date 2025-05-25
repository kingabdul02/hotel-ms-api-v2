<?php

namespace App\Http\Controllers;

use App\Http\Requests\PolicyTypeStoreRequest;
use App\Http\Requests\PolicyTypeUpdateRequest;
use App\Http\Resources\PolicyTypeCollection;
use App\Http\Resources\PolicyTypeResource;
use App\Models\PolicyType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PolicyTypeController extends Controller
{
    public function index(Request $request): PolicyTypeCollection
    {
        $policyTypes = PolicyType::all();

        return new PolicyTypeCollection($policyTypes);
    }

    public function store(PolicyTypeStoreRequest $request): PolicyTypeResource
    {
        $policyType = PolicyType::create($request->validated());

        return new PolicyTypeResource($policyType);
    }

    public function show(Request $request, PolicyType $policyType): PolicyTypeResource
    {
        return new PolicyTypeResource($policyType);
    }

    public function update(PolicyTypeUpdateRequest $request, PolicyType $policyType): PolicyTypeResource
    {
        $policyType->update($request->validated());

        return new PolicyTypeResource($policyType);
    }

    public function destroy(Request $request, PolicyType $policyType): Response
    {
        $policyType->delete();

        return response()->noContent();
    }
}
