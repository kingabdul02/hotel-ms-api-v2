<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CancelationRequestController;
use App\Http\Controllers\DepartmentRequestController;
use App\Http\Controllers\InAppNotificationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PaymentEntryController;
use App\Http\Controllers\RefundLogController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\StatisticCotroller;
use App\Http\Controllers\UserController;
use App\Notifications\ContactNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([
    'prefix' => 'auth',
], function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::post('register', [AuthController::class, 'register']);

    Route::get('email/verify/{token}', [AuthController::class, 'verifyEmail']);

    Route::get('resend-email', [AuthController::class, 'resendVeriificationMail']);

    Route::post('password-reset', [AuthController::class, 'resetPassword']);

    Route::get('reset-password/verify/{token}', [AuthController::class, 'verifyResetToken']);

    Route::post('update-password', [AuthController::class, 'updatePassword']);

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('logout', [AuthController::class, 'logout']);
    });
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::prefix('admin')->group(function () {
        Route::group(['middleware' => ['role:Admin']], function () {
            Route::get('users', [UserController::class, 'index']);

            Route::get('activate-user/{user_id}', [UserController::class, 'activateUser']);
            Route::get('dedactivate-user/{user_id}', [UserController::class, 'deactivateUser']);

            Route::post('process-cancelation-request/{cancelationRequest}', [CancelationRequestController::class, 'proccessCancelationRequest']);

            Route::get('cancelation-requests', [CancelationRequestController::class, 'index']);

            Route::get('cancelation-request/{cancelationRequest}', [CancelationRequestController::class, 'show']);

            Route::get('bookings', [BookingController::class, 'index']);

            Route::get('booking/{booking}', [BookingController::class, 'show']);

            Route::get('check-in-booking/{booking_id}', [BookingController::class, 'checkIn']);

            Route::get('check-out-booking/{booking_id}', [BookingController::class, 'checkOut']);

            Route::get('refund-logs', [RefundLogController::class, 'index']);

            Route::get('refund-log/{refundLog}', [RefundLogController::class, 'show']);

            Route::get('payment-entries', [PaymentEntryController::class, 'index']);

            Route::get('payment-entry/{paymentEntry}', [PaymentEntryController::class, 'show']);

            Route::apiResource('categories', App\Http\Controllers\CategoryController::class);

            Route::apiResource('items', App\Http\Controllers\ItemController::class);

            Route::apiResource('suppliers', App\Http\Controllers\SupplierController::class);

            Route::apiResource('purchase-orders', App\Http\Controllers\PurchaseOrderController::class);

            Route::apiResource('purchase-order-items', App\Http\Controllers\PurchaseOrderItemController::class);

            Route::apiResource('inventories', App\Http\Controllers\InventoryController::class);

            Route::apiResource('inventory-movements', App\Http\Controllers\InventoryMovementController::class);

            Route::apiResource('departments', App\Http\Controllers\DepartmentController::class);

            Route::apiResource('department-requests', App\Http\Controllers\DepartmentRequestController::class);

            Route::post('process-department-request/{departmentRequest}', [DepartmentRequestController::class, 'processRequest']);

            Route::apiResource('rooms', App\Http\Controllers\RoomController::class);

            Route::apiResource('room-types', App\Http\Controllers\RoomTypeController::class);

            Route::post('use-inventory', [InventoryController::class, 'useInventory']);

            Route::prefix('statistics')->group(function () {
                Route::get('booking', [StatisticCotroller::class, 'getBookingStats']);

                Route::get('inventory', [StatisticCotroller::class, 'getInventoryStats']);
            });

            Route::prefix('corporate-booking')->group(function () {
                Route::get('/', [BookingController::class, 'listCorporateBookings']);

                Route::post('/', [BookingController::class, 'corporateBooking']);

                Route::post('/guest/{guest_id}/check-in', [BookingController::class, 'checkInCorporateGuest']);

                Route::post('/guest/{guest_id}/check-out', [BookingController::class, 'checkOutCorporateGuest']);

                Route::get('/billing-report', [BookingController::class, 'generateBillingReport']);

                Route::get('/bill/{reservation_code}', [BookingController::class, 'generateCorporateBill']);

                Route::get('/{corporate_booking_id}', [BookingController::class, 'getCorporateBookingDetails']);

                Route::put('/{corporate_booking_id}', [BookingController::class, 'updateCorporateBooking']);
            });

            Route::post('book-room', [BookingController::class, 'bookRoom']);
        });
    });

    Route::prefix('guest')->group(function () {
        Route::group(['middleware' => ['role:Guest']], function () {
            Route::post('book-room', [BookingController::class, 'bookRoom']);

            Route::get('my-bookings', [BookingController::class, 'getMyBookings']);

            Route::get('get-booking/{booking_id}', [BookingController::class, 'getBooking']);

            Route::post('cancelation-requests', [CancelationRequestController::class, 'store']);

            Route::get('my-profile', [AuthController::class, 'getMyProfile']);

            Route::post('update-profile', [AuthController::class, 'updateProfile']);

            Route::apiResource('saved-bookings', App\Http\Controllers\SavedBookingController::class)->only('store', 'show', 'destroy');
        });
    });

    Route::prefix('manager')->group(function () {
        Route::group(['middleware' => ['role:Manager']], function () {});
    });

    Route::prefix('staff')->group(function () {
        Route::group(['middleware' => ['role:Staff']], function () {});
    });

    Route::get('my-notifications', [InAppNotificationController::class, 'getMyNotification']);

    Route::apiResource('general-settings', App\Http\Controllers\GeneralSettingsController::class);

    Route::post('make-payment/{booking_id}', [PaymentEntryController::class, 'makePament']);
});

Route::post('file-upload', [AuthController::class, 'uploadFile']);

Route::group(['prefix' => 'room'], function () {
    Route::get('search', [RoomController::class, 'search']);

    Route::get('type/{roomType}', [RoomController::class, 'getRoomsByRoomType']);
});

Route::apiResource('hotels', App\Http\Controllers\HotelController::class);

Route::apiResource('hotel-images', App\Http\Controllers\HotelImageController::class);

Route::apiResource('amenities', App\Http\Controllers\AmenityController::class);

Route::apiResource('policy-types', App\Http\Controllers\PolicyTypeController::class);

Route::apiResource('hotel-policies', App\Http\Controllers\HotelPolicyController::class);

Route::apiResource('major-attractions', App\Http\Controllers\MajorAttractionController::class);

// Route::apiResource('reviews', App\Http\Controllers\ReviewController::class);

Route::apiResource('rooms', App\Http\Controllers\RoomController::class)->only('index', 'show');

Route::apiResource('facilities', App\Http\Controllers\FacilityController::class);

Route::apiResource('room-images', App\Http\Controllers\RoomImageController::class);

Route::get('get-room-types', [RoomTypeController::class, 'getRoomType']);

Route::post('send-mail', function (Request $request) {
    $data = [
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'comment' => $request->input('comment'),
    ];

    Log::alert($data);

    Notification::route('mail', 'info@nbteconsult.com')
        ->notify(new ContactNotifier($data));

    return response()->json(['message' => 'Notification sent successfully']);
});
