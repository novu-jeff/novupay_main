# Payment API Integration

## Overview
This API allows you to create and check the status of a payment transaction using the Novopay service.

---

## Create Payment Request
To initiate a payment request, you must send a POST request with the following required parameters.

### **Required Parameters:**
```php
$payload = [
    'service_id' => 'novopay',
    'passwork' => $this->passwork, // Authentication credential
    'amount' => 1000, // Amount to be paid
    'currency' => 'PHP', // Currency type
    'operation_id' => '04', // Unique operation identifier
    'payment_id' => '04', // Unique payment identifier
    'by_method' => 'gcash-qr', // Payment method
    'callback_url' => 'http://your.site/callback_url', // Callback URL for notifications
    'return_url' => 'http://your.site/return_url', // Return URL after payment completion
    'signature' => // Generated in service for security validation
    'customer' => [
        'account_number' => '1234567890', // Customer's account number
        'name' => 'Juan Dela Cruz', // Customer's name
        'email' => 'juan.dela_cruz@gmail.com', // Customer's email
        'phone_number' => '09167608199', // Customer's phone number
        'address' => 'Manila, PH', // Customer's address
    ],
];
```

### **API Endpoint:**
`POST /pay`

### **Response Example:**
```json
{
    "status": "success",
    "trans_id": "2136708",
    "redirect_url": "https://payment-redirect.com",
    "timestamp": "2025-03-09 11:48:59"
}
```

If an error occurs, the response will be:
```json
{
    "status": "fail",
    "error": {
        "message": "operation_id is already taken payment_id is already taken"
    }
}
```

---

## Check Payment Status
To check the status of a payment transaction, send a request with the required parameters.

### **Required Parameters:**
```php
$payload = [
    'service_id' => 'novopay',
    'passwork' => $this->passwork, // Authentication credential
    'operation_id' => '04', // Unique operation identifier
    'signature' => // Generated in service for security validation
];
```

### **API Endpoint:**
`POST /status`

### **Response Example:**
```json
{
    "status": "success",
    "operation": {
        "status": "completed",
        "payment_id": "04",
        "amount": 1000,
        "currency": "PHP",
        "timestamp": "2025-03-09 11:50:00"
    }
}
```

If the operation is not found or has an issue:
```json
{
    "status": "fail",
    "error": {
        "message": "operation_id not found"
    }
}
```

---

## Notes
- Ensure `operation_id` and `payment_id` are unique per transaction.
- The `signature` must be correctly generated to validate the request.
- Use the correct callback and return URLs to handle payment status updates properly.

For further integration details, refer to the official API documentation or contact support.

