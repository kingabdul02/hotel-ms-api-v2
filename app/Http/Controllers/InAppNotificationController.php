<?php

namespace App\Http\Controllers;

use App\Http\Requests\InAppNotificationStoreRequest;
use App\Http\Requests\InAppNotificationUpdateRequest;
use App\Http\Resources\InAppNotificationCollection;
use App\Http\Resources\InAppNotificationResource;
use App\Models\InAppNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InAppNotificationController extends Controller
{
    public function index(Request $request): InAppNotificationCollection
    {
        $inAppNotifications = InAppNotification::all();

        return new InAppNotificationCollection($inAppNotifications);
    }

    public function store(InAppNotificationStoreRequest $request): InAppNotificationResource
    {
        $inAppNotification = InAppNotification::create($request->validated());

        return new InAppNotificationResource($inAppNotification);
    }

    public function show(Request $request, InAppNotification $inAppNotification): InAppNotificationResource
    {
        return new InAppNotificationResource($inAppNotification);
    }

    public function update(InAppNotificationUpdateRequest $request, InAppNotification $inAppNotification): InAppNotificationResource
    {
        $inAppNotification->update($request->validated());

        return new InAppNotificationResource($inAppNotification);
    }

    public function destroy(Request $request, InAppNotification $inAppNotification): Response
    {
        $inAppNotification->delete();

        return response()->noContent();
    }

    public function getMyNotification(Request $request) : InAppNotificationCollection {
        $notifications = InAppNotification::where('user_id', auth()->user()->id)->get();

        return new InAppNotificationCollection($notifications);
    }
}
