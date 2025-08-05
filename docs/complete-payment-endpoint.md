# Complete Payment Endpoint Documentation

## Endpoint

`POST /api/admin/complete-payment`

## Description

This endpoint allows administrators to manually complete payments for bookings using offline payment methods (cash, POS, or bank transfer).

## Authentication

-   Requires authentication (`auth:sanctum` middleware)
-   Requires Admin role

## Request Payload

```json
{
    "booking_id": "string",
    "payment_method": "string"
}
```

### Request Parameters

| Parameter        | Type   | Required | Description                   | Validation                                |
| ---------------- | ------ | -------- | ----------------------------- | ----------------------------------------- |
| `booking_id`     | string | Yes      | The unique booking identifier | Must exist in bookings table              |
| `payment_method` | string | Yes      | The payment method used       | Must be one of: `cash`, `pos`, `transfer` |

## Response

### Success Response (200)

```json
{
    "status": "success",
    "data": {
        "id": 1,
        "booking_id": "BK123456789",
        "user_id": 1,
        "room_id": 1,
        "check_in_date": "2025-08-05",
        "check_out_date": "2025-08-07",
        "total_amount": 1000.0,
        "payment_status": "paid",
        "is_confirmed": true,
        "is_checked_in": false,
        "is_checked_out": false,
        "no_of_guests": 2,
        "no_of_nights": 2,
        "guest_name": "John Doe",
        "is_online_booking": false,
        "created_at": "2025-08-04T10:00:00.000000Z",
        "updated_at": "2025-08-04T10:30:00.000000Z"
    }
}
```

### Error Responses

#### Validation Error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "booking_id": ["The specified booking does not exist."],
        "payment_method": [
            "Payment method must be one of: cash, pos, transfer."
        ]
    }
}
```

#### Booking Not Found (404)

```json
{
    "status": "error",
    "message": "No booking record found"
}
```

#### Payment Entry Not Found (404)

```json
{
    "status": "error",
    "message": "Payment entry not found for the booking"
}
```

## What This Endpoint Does

1. **Validates Input**: Ensures the booking exists and payment method is valid
2. **Updates Room Status**: Marks the room as unavailable and sets check-in/check-out dates
3. **Updates Booking**: Confirms the booking and sets payment status to "paid"
4. **Updates Payment Entry**:
    - Sets payment date to current timestamp
    - Sets payment status to "successful"
    - Sets the appropriate payment method
5. **Creates Transaction Record**: Logs the payment transaction with admin details
6. **Sends Notification**: Notifies the booking user of successful payment (if user exists)

## Payment Method Mapping

| Request Value | Enum Value                  |
| ------------- | --------------------------- |
| `cash`        | `E_PaymentMethod::CASH`     |
| `pos`         | `E_PaymentMethod::POS`      |
| `transfer`    | `E_PaymentMethod::TRANSFER` |

## Example Usage

```bash
curl -X POST "/api/admin/complete-payment" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "booking_id": "BK123456789",
    "payment_method": "cash"
  }'
```
