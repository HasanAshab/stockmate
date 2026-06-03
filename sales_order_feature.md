## Sales Order System with SSLCommerz — Implementation Instructions

### Package Installation
sslcommerz/sslcommerz-laravel Package is already installed and setted up for you

### Package Knowledge: [docs](https://hasinhayder.github.io/sslcommerz-laravel/docs.html)
Note: this may example of traditional websites but this project is restful stateless api

---

### Overview
A sales order is created when goods are sold to a customer. Stock does not
decrement when the order is created. Stock only decrements after SSLCommerz
confirms the payment as successful. If payment fails or is cancelled, stock
is untouched and the order is marked accordingly.

---

### Status Flow (The SalesOrderStatus enum)
pending → paid → failed → cancelled

- pending: order created, awaiting payment
- paid: SSLCommerz confirmed payment success, stock decremented
- failed: SSLCommerz returned a failed or invalid response
- cancelled: customer cancelled on the payment page

---

### Database

#### New table: sales_orders
- id (bigint, primary key)
- customer_name (string, required)
- customer_email (string, required)
- customer_phone (string, nullable)
- warehouse_id (foreign key → warehouses.id) — stock deducted from this warehouse
- status (unsingedTinyInteger)
- total_amount (decimal 10,2)
- transaction_id (string, nullable) — SSLCommerz transaction ID on success
- payment_payload (json, nullable) — full SSLCommerz response stored for audit
- created_by (foreign key → users.id)
- timestamps

#### New table: sales_order_items
- id (bigint, primary key)
- sales_order_id (foreign key → sales_orders.id, cascade delete)
- product_id (foreign key → products.id)
- quantity (integer, required, min 1)
- unit_price (decimal 10,2, required)
- timestamps

---

### Models

#### SalesOrder model
- fillable: customer_name, customer_email, customer_phone, warehouse_id, status, total_amount, transaction_id, payment_payload, created_by
- 'status' -> SalesOrderStatus enum, default SalesOrderStatus::Pending
- cast payment_payload as array
- belongsTo Warehouse
- belongsTo User (as createdBy)
- hasMany SalesOrderItem

#### SalesOrderItem model
- fillable: sales_order_id, product_id, quantity, unit_price
- belongsTo SalesOrder
- belongsTo Product

---

### Middleware & Access Control
- POST /sales-orders (create): admin and staff
- GET /sales-orders (list): admin and staff
- GET /sales-orders/{id}: admin and staff
- PATCH /sales-orders/{id}/cancel: admin only
- POST /sales-orders/{id}/initiate-payment: admin and staff
- POST /payment/success: no auth required (SSLCommerz posts here)
- POST /payment/fail: no auth required
- POST /payment/cancel: no auth required

---

### API Endpoints

#### GET /sales-orders
- Admin and staff.
- Paginate at 15 per page.
- Support filters: status (query param), from and to date on created_at.
- Return each order: id, customer name, customer email, warehouse name, status, total_amount, transaction_id, item count, created_by name, created_at.

#### POST /sales-orders
- Admin and staff.
- Request body: customer_name (required), customer_email (required, email), customer_phone (nullable), warehouse_id (required, exists), items (required, array min 1).
- Each item: product_id (required, exists), quantity (required, integer min 1), unit_price (required, numeric min 0).
- Before creating the order, validate stock availability for each item against warehouse_stock. If any product has insufficient stock in the given warehouse, return 422 with message "Insufficient stock for product {product name} in selected warehouse."
- Calculate total_amount as sum of (quantity * unit_price) for all items.
- Create SalesOrder with status pending and created_by set to authenticated user id.
- Create one SalesOrderItem per item.
- Do not touch stock at this point.
- Return 201 with the created order and items.

#### GET /sales-orders/{id}
- Admin and staff.
- Return full order with warehouse, createdBy, and all items (product name, sku, quantity, unit_price).
- Return 404 if not found.

#### POST /sales-orders/{id}/initiate-payment
- Admin and staff.
- Only allowed if status is pending.
- Build the SSLCommerz payment payload using the package. Required fields:
  - total_amount: order total_amount
  - currency: BDT
  - tran_id: a unique transaction reference (e.g. "SO-{order_id}-{timestamp}")
  - success_url: POST /api/payment/success
  - fail_url: POST /api/payment/fail
  - cancel_url: POST /api/payment/cancel
  - cus_name: customer_name
  - cus_email: customer_email
  - cus_phone: customer_phone or "N/A"
  - cus_add1: "Dhaka"
  - cus_city: "Dhaka"
  - cus_country: "Bangladesh"
  - shipping_method: "NO"
  - product_name: "Sales Order #{order_id}"
  - product_category: "General"
  - product_profile: "general"
- Call the SSLCommerz package to generate the payment URL.
- Return 200 with the payment URL so the client can redirect the customer.

#### POST /payment/success
- No auth required. SSLCommerz posts here after successful payment.
- Verify the payment with SSLCommerz using the val_id from the POST body and the package's validation method.
- If validation fails, return 400 with message "Payment validation failed."
- Find the SalesOrder by matching tran_id from the POST body.
- If order not found or status is not pending, return 400.
- Use DB transaction:
  1. For each SalesOrderItem, re-check warehouse_stock. If any product now has insufficient stock, abort and return 422.
  2. For each item, decrement warehouse_stock quantity.
  3. Create a stock_log entry for each item: type out, warehouse_id from SalesOrder, product_id, user_id set to created_by of the SalesOrder, quantity, note = "Sold via Sales Order #{sales_order_id}".
  4. Check isLow() on each warehouse_stock row after decrement. If low, send LowStockNotification to all admin users.
  5. Set SalesOrder status to paid, store transaction_id and full POST body in payment_payload.
- Return 200 with message "Payment successful."

#### POST /payment/fail
- No auth required.
- Find the SalesOrder by tran_id from POST body.
- Set status to failed.
- Store full POST body in payment_payload.
- Return 200 with message "Payment failed."

#### POST /payment/cancel
- No auth required.
- Find the SalesOrder by tran_id from POST body.
- Set status to cancelled.
- Store full POST body in payment_payload.
- Return 200 with message "Payment cancelled."

#### PATCH /sales-orders/{id}/cancel
- Admin only.
- Only allowed if status is pending.
- If status is paid, return 422 with message "Cannot cancel a paid order."
- Set status to cancelled.
- Return 200 with message "Sales order cancelled."

---

### Important Rules
- Stock is checked on order creation but not reserved. Re-check stock inside the success callback before decrementing. This prevents overselling if two orders are placed simultaneously.
- Always use DB transaction inside the success callback. If stock decrement fails for any item, roll back everything and do not mark the order as paid.
- Store the full SSLCommerz POST body in payment_payload on all three callbacks (success, fail, cancel) for audit purposes.
- Never decrement stock on fail or cancel callbacks.
- The success callback must validate the payment with SSLCommerz using val_id before doing anything. Never trust the POST body alone.