<?php

namespace App\Http\Controllers;

use App\Http\Requests\RefundLogStoreRequest;
use App\Http\Requests\RefundLogUpdateRequest;
use App\Http\Resources\RefundLogCollection;
use App\Http\Resources\RefundLogResource;
use App\Models\RefundLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RefundLogController extends Controller
{
    public function index(Request $request): RefundLogCollection
    {
        $refundLogs = RefundLog::all();

        return new RefundLogCollection($refundLogs);
    }

    public function show(Request $request, RefundLog $refundLog): RefundLogResource
    {
        return new RefundLogResource($refundLog);
    }
}
