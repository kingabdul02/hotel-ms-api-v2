<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequestStoreRequest;
use App\Http\Requests\DepartmentRequestUpdateRequest;
use App\Http\Resources\DepartmentRequestCollection;
use App\Http\Resources\DepartmentRequestResource;
use App\Models\DepartmentRequest;
use App\Services\InventoryService;
use App\Traits\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DepartmentRequestController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {
    }

    use JsonResponse;

    public function index(Request $request): DepartmentRequestCollection
    {
        $departmentRequests = DepartmentRequest::all();

        return new DepartmentRequestCollection($departmentRequests);
    }

    public function store(DepartmentRequestStoreRequest $request): DepartmentRequestResource
    {
        $departmentRequest = DepartmentRequest::create($request->validated());

        return new DepartmentRequestResource($departmentRequest);
    }

    public function show(Request $request, DepartmentRequest $departmentRequest): DepartmentRequestResource
    {
        return new DepartmentRequestResource($departmentRequest);
    }

    public function update(DepartmentRequestUpdateRequest $request, DepartmentRequest $departmentRequest): DepartmentRequestResource
    {
        $departmentRequest->update($request->validated());

        return new DepartmentRequestResource($departmentRequest);
    }

    public function destroy(Request $request, DepartmentRequest $departmentRequest): Response
    {
        $departmentRequest->delete();

        return response()->noContent();
    }

    public function processRequest(DepartmentRequest $departmentRequest, Request $request)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'approved_quantity' => 'required|integer',
        ]);

        if ($request->status == 'Approved') {
            try {
                $remarks = 'Issued to department request #'.$departmentRequest->id;
                $inventoryMovement = $this->inventoryService->processInventory(
                    $departmentRequest->item_id,
                    'OUT',
                    $request->approved_quantity,
                    now(),
                    $remarks
                );

                if ($inventoryMovement instanceof \Illuminate\Http\JsonResponse) {
                    return $inventoryMovement;
                } else {
                    $departmentRequest->status = 'Fulfilled';
                }

            } catch (\Exception $e) {
                return $this->error('Error processing request: '.$e->getMessage());
            }
        } else {
            $departmentRequest->status = 'Rejected';
        }

        $departmentRequest->save();

        return response()->json([
            'message' => 'Department request processed successfully.',
            'request' => $departmentRequest,
        ]);
    }
}
