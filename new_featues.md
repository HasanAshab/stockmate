## Purchase Order System — Implementation Instructions

### Overview
A purchase order (PO) is raised when the business wants to reorder stock from a
supplier. Stock levels do not change when a PO is created or sent. Stock only
updates when the PO is received. A PO can be partially received — stock updates
each time a partial receipt is recorded until the order is fully received or
manually closed.

---

### Status Flow
draft → ordered → partially_received → received → cancelled

- draft: PO created but not yet sent to supplier
- ordered: PO has been communicated to supplier
- partially_received: at least one receipt recorded but not all items fully received
- received: all items fully received
- cancelled: PO cancelled at any point before received

Once a PO is marked as received, it cannot be edited or cancelled.
Once a PO is cancelled, it cannot be reopened.

---

### Database + models

#### created for you: purchase_orders, purchase_order_items
---

### Middleware & Access Control
- All PO endpoints: admin only
- Receiving a PO: admin and staff

---

### API Endpoints

#### GET /purchase-orders
- Admin only.
- Paginate at 15 per page.
- Support filters: status (query param), supplier_id (query param), from and to date range on created_at.
- Return each PO: id, supplier name, warehouse name, status, item count, note, ordered_at, received_at, created_by name, created_at.

#### POST /purchase-orders
- Admin only.
- Request body: supplier_id (required, exists), warehouse_id (required, exists), note (nullable), items (required, array, min 1 item).
- Each item in items: product_id (required, exists), ordered_quantity (required, integer min 1), unit_cost (required, numeric min 0).
- Create PurchaseOrder with status draft and created_by set to authenticated user id.
- Create one PurchaseOrderItem per item in the array.
- Return 201 with the created PO and its items.

#### GET /purchase-orders/{id}
- Admin only.
- Return PO with supplier, warehouse, createdBy, and all items (each with product name, sku, ordered_quantity, received_quantity, unit_cost, isFullyReceived).
- Return 404 if not found.

#### PUT /purchase-orders/{id}
- Admin only.
- Only allowed if status is draft or ordered.
- If status is received or cancelled, return 422 with message "This purchase order can no longer be edited."
- Allowed fields: supplier_id, warehouse_id, note, items.
- If items is provided, delete all existing PurchaseOrderItems and recreate from the new array.
- Return 200 with updated PO and items.

#### PATCH /purchase-orders/{id}/mark-ordered
- Admin only.
- Only allowed if current status is draft.
- Set status to ordered and set ordered_at to current timestamp.
- Return 200 with updated PO.

#### PATCH /purchase-orders/{id}/cancel
- Admin only.
- Only allowed if status is draft or ordered.
- If status is partially_received, received, return 422 with message "Cannot cancel a PO that has already received stock."
- Set status to cancelled.
- Return 200 with message "Purchase order cancelled."

#### POST /purchase-orders/{id}/receive
- Admin and staff.
- Only allowed if status is ordered or partially_received.
- Request body: items (required, array). Each item: purchase_order_item_id (required, exists), quantity_received (required, integer min 1).
- For each item in the request:
  1. Find the PurchaseOrderItem by purchase_order_item_id.
  2. Calculate how much is still outstanding: outstanding = ordered_quantity - received_quantity.
  3. If quantity_received exceeds outstanding, return 422 with message "Received quantity exceeds outstanding quantity for product {product name}."
  4. Add quantity_received to received_quantity on the PurchaseOrderItem.
  5. Use firstOrCreate on warehouse_stock for warehouse_id + product_id (default quantity 0).
  6. Increment warehouse_stock quantity by quantity_received.
  7. Create a stock_log entry: type in, warehouse_id from PO, product_id, user_id (authenticated user), quantity = quantity_received, unit_cost from PurchaseOrderItem, note = "Received from PO #{purchase_order_id}".
- Wrap all of the above in a DB transaction.
- After processing all items, check if every PurchaseOrderItem on this PO is fully received.
  - If all fully received: set PO status to received and received_at to current timestamp.
  - If not all fully received: set PO status to partially_received.
- Return 200 with message "Stock received." and updated PO.

---

### Important Rules
- Stock is never updated when a PO is created, edited, or marked as ordered. Only the receive endpoint touches stock.
- Always use DB transaction in the receive endpoint. If any item fails, roll back everything.
- received_quantity on PurchaseOrderItem is cumulative. Each call to receive adds to it, it does not reset.
- A PO item cannot receive more than its ordered_quantity in total across all receipt calls.
- Creating a stock_log entry on every receipt ensures the warehouse stock history remains complete.