# Vendor_CustomOrderProcessing

## Overview
This module extends the Magento 2 order processing workflow by:
- Exposing a secure REST API to update order statuses
- Logging all order status changes to a custom table
- Check if status is allowed before setting status on order
- Sending notification emails when an order is marked as "shipped"

---

## installation

1. Copy files to: `app/code/Vendor/CustomOrderProcessing`
2. Run the following commands:
```bash
bin/magento module:enable Vendor_CustomOrderProcessing
bin/magento setup:upgrade
bin/magento cache:flush
```

---

## API Usage

**Endpoint:**
```
POST /rest/V1/customorder/update-status
```

**Payload:**
```json
{
  "orderIncrementId": "000000123",
  "status": "processing"
}
```

**Headers:**
```
Authorization: Bearer <admin-token>
Content-Type: application/json
```

---

## Email Notifications

If the order status is set to `"shipped"`, the module sends a notification to the customer.

You must configure the email template with identifier:
```
order_shipped_email_template
```

---

## Architectural Notes

- Order status changes are handled using the `sales_order_save_after` event.
- Order data is validated using Magento’s `OrderRepositoryInterface` .
- Status change logs are inserted into a custom table `custom_order_status_log`.
- The email system uses Magento’s built-in `TransportBuilder`.


