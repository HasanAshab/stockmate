# StockMate Frontend - Complete Implementation Guide

## Project Overview

Build a React SPA with TailwindCSS that connects to the StockMate Laravel REST API. This is an internal warehouse management system for a trading business with multiple warehouses across Dhaka. The system supports two user roles: **Admin** and **Staff**, each with different access levels.

---

## Tech Stack

- **Framework**: React 18+ with Vite
- **Routing**: React Router v6
- **Styling**: TailwindCSS v3
- **State Management**: TanStack Query (React Query) v5 for server state
- **Form Handling**: React Hook Form with Zod validation
- **HTTP Client**: Axios
- **UI Components**: Headless UI + custom components
- **Icons**: Heroicons
- **Notifications**: React Hot Toast
- **Date Handling**: date-fns

---

## API Base Configuration

**Base URL**: `http://localhost/api/v1` (configurable via `.env`)
**Authentication**: Laravel Sanctum (Bearer Token)

### JSON:API Response Format
All API responses follow JSON:API specification:
```json
{
  "data": {
    "id": "1",
    "type": "products",
    "attributes": {
      "name": "Product Name",
      "sku": "SKU123"
    },
    "relationships": {
      "category": {
        "data": { "id": "1", "type": "categories" }
      }
    }
  }
}
```

For collections:
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "...",
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

---

## Authentication & Authorization

### User Roles
- **Admin** (role: 2): Full system access
- **Staff** (role: 1): Limited access, cannot manage users, categories, suppliers, warehouses, or cancel orders

### Login Flow
**Endpoint**: `POST /api/v1/auth/login`
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response**:
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": 2,
    "is_active": true
  }
}
```

**Error Cases**:
- Inactive account: `{"message": "Your account has been deactivated."}`
- Invalid credentials: `{"message": "These credentials do not match our records."}`

### Logout
**Endpoint**: `POST /api/v1/auth/logout`

### Token Storage
- Store token in localStorage: `authToken`
- Store user data in localStorage: `authUser`
- Include token in all requests: `Authorization: Bearer {token}`

### Protected Routes
Create a `<ProtectedRoute>` component that:
1. Checks for token
2. Redirects to `/login` if no token
3. Validates role for admin-only routes

---

## Page-by-Page Implementation

### 1. Login Page (`/login`)

**Features**:
- Email and password fields
- "Remember me" checkbox (optional)
- Form validation
- Show error message if account is inactive
- Redirect to `/dashboard` on success

**Validation**:
- Email: required, valid email format
- Password: required, min 6 characters

---

### 2. Dashboard (`/dashboard`)

**Endpoint**: `GET /api/v1/dashboard`

**Response**:
```json
{
  "products_count": 150,
  "warehouses_count": 5,
  "suppliers_count": 30,
  "low_stock_count": 12,
  "recent_stock_logs": [
    {
      "id": 1,
      "product_name": "Product A",
      "type": "in",
      "quantity": 50,
      "warehouse_name": "Warehouse 1",
      "user_name": "John Doe",
      "created_at": "2026-06-05T10:30:00Z"
    }
  ]
}
```

**UI Layout**:
- 4 metric cards in a grid (products, warehouses, suppliers, low stock)
- Low stock card should be styled as warning (yellow/orange)
- Low stock card is clickable → redirects to `/reports/low-stock`
- Below cards: table of 5 most recent stock logs
- Columns: Product, Type (badge: green for "In", red for "Out"), Quantity, Warehouse, Recorded By, Date

---

### 3. User Management (`/users`) - Admin Only

#### List Users
**Endpoint**: `GET /api/v1/users?page=1`

**Display**:
- Table with columns: Name, Email, Role (badge), Status (badge: green=active, gray=inactive), Created At
- "Create User" button (top right)
- Toggle status button on each row (cannot toggle self)
- Edit button on each row

#### Create User
**Endpoint**: `POST /api/v1/users`

**Fields**:
```json
{
  "name": "New User",
  "email": "user@example.com",
  "password": "password123",
  "role": 2
}
```

**Role dropdown**: Admin (2), Staff (1)

#### Update User
**Endpoint**: `PATCH /api/v1/users/{id}`

**Fields**:
```json
{
  "name": "Updated Name",
  "email": "updated@example.com",
  "role": 2,
  "password": "newpassword" // optional
}
```

#### Toggle Status
**Endpoint**: `PATCH /api/v1/users/{id}/toggle-status`

**Business Rules**:
- Admin cannot toggle their own status
- Admin cannot change their own role

---

### 4. Profile (`/profile`)

**Endpoints**:
- `GET /api/v1/profile`
- `PATCH /api/v1/profile`

**Fields** (editable):
- Name
- Password (optional, with confirmation)

**Fields** (read-only):
- Email
- Role (display as badge)

---

### 5. Warehouses (`/warehouses`) - Admin Only

#### List Warehouses
**Endpoint**: `GET /api/v1/warehouses`

**Display**:
- Table: Name, Location, Status (toggle), Actions
- Create button
- View Stock button on each row → `/warehouses/{id}/stock`

#### Create/Update Warehouse
**Endpoints**:
- `POST /api/v1/warehouses`
- `PATCH /api/v1/warehouses/{id}`

**Fields**:
```json
{
  "name": "Warehouse Name",
  "location": "Dhaka, Bangladesh",
  "is_active": true
}
```

#### Delete Warehouse
**Endpoint**: `DELETE /api/v1/warehouses/{id}`

**Error**: "Cannot delete warehouse with existing stock" (422)

#### View Warehouse Stock
**Endpoint**: `GET /api/v1/warehouses/{id}/stock`

**Response**:
```json
{
  "data": [
    {
      "product_id": 1,
      "product_name": "Product A",
      "sku": "SKU001",
      "quantity": 50,
      "reorder_threshold": 10,
      "is_low": false
    }
  ]
}
```

**Display**:
- Table: Product, SKU, Quantity, Reorder Threshold, Status
- Highlight low stock rows in yellow/orange

---

### 6. Categories (`/categories`) - Admin Only

#### List Categories
**Endpoint**: `GET /api/v1/categories`

**Display**:
- Table: Name, Description, Actions
- Create, Edit, Delete buttons

#### Create/Update Category
**Endpoints**:
- `POST /api/v1/categories`
- `PATCH /api/v1/categories/{id}`

**Fields**:
```json
{
  "name": "Category Name",
  "description": "Description"
}
```

#### Delete Category
**Endpoint**: `DELETE /api/v1/categories/{id}`

**Error**: "Cannot delete category with products" (422)

---

### 7. Suppliers (`/suppliers`) - Admin Only

#### List Suppliers
**Endpoint**: `GET /api/v1/suppliers`

**Display**:
- Table: Name, Phone, Email, Address, Actions
- Create, Edit, Delete buttons

#### Create/Update Supplier
**Endpoints**:
- `POST /api/v1/suppliers`
- `PATCH /api/v1/suppliers/{id}`

**Fields**:
```json
{
  "name": "Supplier Name",
  "phone": "+880123456789",
  "email": "supplier@example.com",
  "address": "Dhaka, Bangladesh"
}
```

#### Delete Supplier
**Endpoint**: `DELETE /api/v1/suppliers/{id}`

**Error**: "Cannot delete supplier with products" (422)

---

### 8. Products (`/products`)

#### List Products
**Endpoint**: `GET /api/v1/products?filter[category]=1&filter[supplier]=2&filter[search]=keyword&page=1`

**Display**:
- Grid or table view
- Image thumbnail, Name, SKU, Category, Supplier, Price
- Filters: Category (dropdown), Supplier (dropdown), Search (name/SKU)
- **Admin**: Create, Edit, Delete buttons
- **Staff**: View only

#### Create Product (Admin Only)
**Endpoint**: `POST /api/v1/products`

**Fields** (multipart/form-data):
```json
{
  "name": "Product Name",
  "sku": "SKU001",
  "category_id": 1,
  "supplier_id": 1,
  "price": 100.50,
  "image": File
}
```

#### Update Product (Admin Only)
**Endpoint**: `PATCH /api/v1/products/{id}`

**Fields**: Same as create (image optional)

#### Delete Product (Admin Only)
**Endpoint**: `DELETE /api/v1/products/{id}`

---

### 9. Stock Management (`/stock`)

#### Stock In (`/stock/in`)
**Endpoint**: `POST /api/v1/stock-logs`

**Fields**:
```json
{
  "warehouse_id": 1,
  "product_id": 1,
  "type": "in",
  "quantity": 50,
  "unit_cost": 10.50,
  "note": "Optional note"
}
```

**UI Features**:
- Warehouse dropdown
- Product dropdown (searchable) or SKU scanner input
- SKU scanner: auto-detect when SKU is entered, auto-populate product
- Quantity input
- Unit cost input (optional)
- Note textarea (optional)

#### Stock Out (`/stock/out`)
**Endpoint**: `POST /api/v1/stock-logs`

**Fields**:
```json
{
  "warehouse_id": 1,
  "product_id": 1,
  "type": "out",
  "quantity": 20,
  "note": "Optional note"
}
```

**Validation**: Check available stock before submitting

**Error**: "Not enough stock available in this warehouse" (422)

#### Transfer Stock (`/stock/transfer`) - Admin Only
**Endpoint**: `POST /api/v1/stock-logs/transfer`

**Fields**:
```json
{
  "from_warehouse_id": 1,
  "to_warehouse_id": 2,
  "product_id": 1,
  "quantity": 10,
  "note": "Optional note"
}
```

---

### 10. Stock Log (`/stock-logs`)

**Endpoint**: `GET /api/v1/stock-logs?filter[product]=1&filter[type]=in&filter[warehouse]=1&filter[from]=2026-01-01&filter[to]=2026-12-31&page=1`

**Display**:
- Paginated table
- Columns: Product Name, SKU, Type (badge), Quantity, Warehouse, Recorded By, Note, Date & Time
- Filters: Product (dropdown), Type (dropdown: In/Out), Warehouse (dropdown), Date Range Picker
- Export button (not implemented in current API)

---

### 11. Activity Log (`/activity-logs`) - Admin Only

**Endpoint**: `GET /api/v1/activity-logs?filter[user]=1&filter[from]=2026-01-01&filter[to]=2026-12-31&page=1`

**Display**:
- Paginated table
- Columns: User, Description, Date & Time
- Filters: User (dropdown), Date Range

---

### 12. Reports (`/reports`) - Admin Only

#### Stock Summary (`/reports/stock-summary`)
**Note**: This endpoint may not exist yet. You may need to aggregate data from `/api/v1/warehouses/{id}/stock` for each warehouse.

**Display**:
- Table: Product, SKU, Warehouse columns (one per warehouse), Total Quantity, Status
- Highlight low stock rows
- Export CSV/Excel button

#### Low Stock (`/reports/low-stock`)
**Endpoint**: `GET /api/v1/products?filter[low_stock]=true` or aggregate from warehouse stock

**Display**:
- Table: Product, SKU, Warehouse, Current Quantity, Reorder Threshold
- All rows highlighted as warning

#### Stock Movement (`/reports/stock-movement`)
**Endpoint**: `GET /api/v1/stock-logs/export/{format}?from=2026-01-01&to=2026-12-31`

**Display**:
- Date range picker
- Export CSV/Excel button
- Opens exported file in new tab

---

### 13. Purchase Orders (`/purchase-orders`) - Admin Only (Receive: Admin + Staff)

#### List Purchase Orders
**Endpoint**: `GET /api/v1/purchase-orders?filter[status]=1&filter[supplier_id]=1&filter[created_at][from]=2026-01-01&filter[created_at][to]=2026-12-31&page=1`

**Display**:
- Table: PO ID, Supplier, Warehouse, Status (badge), Total Items, Created Date, Actions
- Filters: Status (dropdown), Supplier (dropdown), Date Range
- Create button (Admin only)

**Status Values & Colors**:
- Draft (1): Gray
- Ordered (2): Blue
- PartiallyReceived (3): Yellow
- Received (4): Green
- Cancelled (5): Red

#### Create Purchase Order (Admin Only)
**Endpoint**: `POST /api/v1/purchase-orders`

**Fields**:
```json
{
  "supplier_id": 1,
  "warehouse_id": 1,
  "note": "Optional",
  "items": [
    {
      "product_id": 1,
      "ordered_quantity": 100,
      "unit_cost": 10.50
    }
  ]
}
```

**UI**: Dynamic form with "Add Item" button for multiple products

#### View Purchase Order
**Endpoint**: `GET /api/v1/purchase-orders/{id}`

**Display**:
- PO details: Supplier, Warehouse, Status, Note, Dates
- Items table: Product, SKU, Ordered Qty, Received Qty, Unit Cost, Fully Received (badge)
- Action buttons based on status:
  - Draft: Edit, Mark as Ordered, Cancel
  - Ordered: Receive, Cancel
  - Partially Received: Receive

#### Update Purchase Order (Admin Only)
**Endpoint**: `PATCH /api/v1/purchase-orders/{id}`

**Conditions**: Only if status is Draft or Ordered

#### Mark as Ordered (Admin Only)
**Endpoint**: `PATCH /api/v1/purchase-orders/{id}/mark-ordered`

**Conditions**: Only if status is Draft

#### Cancel Purchase Order (Admin Only)
**Endpoint**: `PATCH /api/v1/purchase-orders/{id}/cancel`

**Conditions**: Only if status is Draft or Ordered (not PartiallyReceived or Received)

#### Receive Purchase Order (Admin + Staff)
**Endpoint**: `POST /api/v1/purchase-orders/{id}/receive`

**Fields**:
```json
{
  "items": [
    {
      "purchase_order_item_id": 1,
      "quantity_received": 50
    }
  ]
}
```

**UI**:
- Show only outstanding items (ordered_qty - received_qty > 0)
- Input field for received quantity
- Validation: cannot exceed outstanding quantity
- Success: toast "Stock received" + refresh PO

---

### 14. Sales Orders (`/sales-orders`) - Admin + Staff

#### List Sales Orders
**Endpoint**: `GET /api/v1/sales-orders?filter[status]=1&filter[created_at][from]=2026-01-01&filter[created_at][to]=2026-12-31&page=1`

**Display**:
- Table: Order ID, Customer Name, Warehouse, Status (badge), Total Amount, Created Date, Actions
- Filters: Status (dropdown), Date Range
- Create button

**Status Values & Colors**:
- Pending (1): Yellow
- Paid (2): Green
- Failed (3): Red
- Cancelled (4): Gray

#### Create Sales Order
**Endpoint**: `POST /api/v1/sales-orders`

**Fields**:
```json
{
  "customer_name": "Customer Name",
  "customer_email": "customer@example.com",
  "customer_phone": "+880123456789",
  "warehouse_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 10,
      "unit_price": 15.00
    }
  ]
}
```

**Validation**: Check stock availability before submitting

**Error**: "Insufficient stock for product {name} in selected warehouse" (422)

#### View Sales Order
**Endpoint**: `GET /api/v1/sales-orders/{id}`

**Display**:
- Customer info: Name, Email, Phone
- Warehouse
- Items table: Product, SKU, Quantity, Unit Price, Subtotal
- Total Amount
- Payment Status (badge)
- Transaction ID (if paid)
- Timestamps
- Action buttons:
  - Pending: "Pay Now" button, "Cancel" button (Admin only)
  - Others: Read-only

#### Initiate Payment
**Endpoint**: `POST /api/v1/sales-orders/{id}/initiate-payment`

**Response**:
```json
{
  "payment_url": "https://sandbox.sslcommerz.com/...",
  "transaction_id": "SO-1-1234567890"
}
```

**Action**: Redirect user to `payment_url` in new window or current window

#### Cancel Sales Order (Admin Only)
**Endpoint**: `PATCH /api/v1/sales-orders/{id}/cancel`

**Conditions**: Only if status is Pending

---

## Global UI Components

### Navigation Sidebar
**Links based on role**:

**All Users**:
- Dashboard
- Stock Management (In, Out, Transfer if Admin)
- Stock Log
- Products
- Profile

**Admin Only**:
- Users
- Warehouses
- Categories
- Suppliers
- Reports
- Purchase Orders
- Activity Log

**Admin + Staff**:
- Sales Orders

### Logout Button
- Visible in navbar/sidebar
- Clears token and user from localStorage
- Redirects to `/login`

### Pagination Component
- Shows page numbers with prev/next buttons
- Displays "Showing X to Y of Z results"
- Updates URL query params on page change

### Filter Bar Component
- Reusable component for date range, dropdown filters
- Updates URL query params
- Persists filters on navigation

### Toast Notifications
- Success: Green
- Error: Red
- Warning: Yellow
- Info: Blue

### Confirmation Modals
- For delete actions
- For status toggles
- For cancellation actions

### Loading States
- Skeleton loaders for tables
- Spinner for forms
- Full-page loader for initial app load

---

## Error Handling

### HTTP Status Codes
- **401 Unauthorized**: Redirect to `/login`, clear token
- **403 Forbidden**: Show error toast "You don't have permission"
- **422 Unprocessable Entity**: Display validation errors on form
- **500 Server Error**: Show error toast "Something went wrong"

### Validation Error Format
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "quantity": ["Not enough stock available."]
  }
}
```

**Display**: Show errors inline under each field

---

## Query Parameters & Filters

### Pagination
- `?page=1`

### Filters
- `?filter[status]=1`
- `?filter[category]=1`
- `?filter[supplier_id]=2`
- `?filter[search]=keyword`
- `?filter[from]=2026-01-01`
- `?filter[to]=2026-12-31`
- `?filter[created_at][from]=2026-01-01&filter[created_at][to]=2026-12-31`

### Sorting
- `?sort=-created_at` (descending)
- `?sort=name` (ascending)

### Includes
- `?include=category,supplier`

---

## File Upload

### Product Images
- Accept: image/jpeg, image/png, image/gif
- Max size: 2MB
- Use `FormData` with `Content-Type: multipart/form-data`

```javascript
const formData = new FormData();
formData.append('name', 'Product Name');
formData.append('image', fileInput.files[0]);

axios.post('/api/v1/products', formData, {
  headers: { 'Content-Type': 'multipart/form-data' }
});
```

---

## Mobile & Tablet Responsiveness

- Desktop: Full sidebar + main content
- Tablet: Collapsible sidebar
- Mobile: Hidden sidebar with hamburger menu
- Tables: Horizontal scroll on mobile
- Forms: Stack fields vertically on mobile

---

## Security Best Practices

- **Never** store sensitive data in localStorage beyond token
- **Always** validate user permissions client-side AND rely on server-side validation
- **Always** sanitize user inputs before rendering (React does this by default)
- Use HTTPS in production
- Implement CSRF protection via `sanctum/csrf-cookie` if using cookies

---

## Development Workflow

### Project Setup
```bash
npm create vite@latest stockmate-frontend -- --template react
cd stockmate-frontend
npm install axios react-router-dom @tanstack/react-query react-hook-form zod @hookform/resolvers
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
npm install @headlessui/react @heroicons/react react-hot-toast date-fns
```

### Environment Variables
Create `.env`:
```
VITE_API_BASE_URL=http://localhost/api/v1
```

### Folder Structure
```
src/
├── api/
│   ├── axios.js
│   └── endpoints/
│       ├── auth.js
│       ├── users.js
│       ├── products.js
│       └── ...
├── components/
│   ├── layout/
│   │   ├── Sidebar.jsx
│   │   ├── Navbar.jsx
│   │   └── Layout.jsx
│   ├── ui/
│   │   ├── Button.jsx
│   │   ├── Input.jsx
│   │   ├── Table.jsx
│   │   ├── Modal.jsx
│   │   └── ...
│   └── ...
├── pages/
│   ├── auth/
│   │   └── Login.jsx
│   ├── dashboard/
│   │   └── Dashboard.jsx
│   ├── users/
│   │   ├── UserList.jsx
│   │   ├── UserForm.jsx
│   │   └── ...
│   └── ...
├── hooks/
│   ├── useAuth.js
│   ├── usePermissions.js
│   └── ...
├── utils/
│   ├── formatters.js
│   ├── validators.js
│   └── ...
├── routes/
│   ├── AppRoutes.jsx
│   └── ProtectedRoute.jsx
├── App.jsx
└── main.jsx
```

---

## Testing Checklist

### Authentication
- [ ] Login with valid credentials
- [ ] Login with inactive account shows error
- [ ] Login with invalid credentials shows error
- [ ] Logout clears token and redirects
- [ ] Unauthorized access redirects to login

### User Management (Admin)
- [ ] List all users with pagination
- [ ] Create new user
- [ ] Update user details
- [ ] Toggle user status (not self)
- [ ] Cannot change own role

### Stock Operations
- [ ] Stock in records correctly
- [ ] Stock out validates quantity
- [ ] Transfer updates both warehouses
- [ ] SKU scanner auto-populates product

### Purchase Orders
- [ ] Create PO with multiple items
- [ ] Mark PO as ordered
- [ ] Receive PO partially
- [ ] Receive PO fully updates status
- [ ] Cannot cancel PO after receiving stock

### Sales Orders
- [ ] Create SO validates stock
- [ ] Initiate payment redirects to SSLCommerz
- [ ] Payment success updates order and decrements stock
- [ ] Payment fail updates order status
- [ ] Cannot cancel paid order

### Reports
- [ ] Low stock report shows correct data
- [ ] Stock movement export works
- [ ] Activity log filters correctly

### Permissions
- [ ] Staff cannot access admin-only pages
- [ ] Staff cannot see admin-only buttons
- [ ] API returns 403 for unauthorized actions

---

## API Response Examples

### Dashboard
```json
{
  "products_count": 150,
  "warehouses_count": 5,
  "suppliers_count": 30,
  "low_stock_count": 12,
  "recent_stock_logs": [...]
}
```

### JSON:API Single Resource
```json
{
  "data": {
    "id": "1",
    "type": "products",
    "attributes": {
      "name": "Product Name",
      "sku": "SKU001",
      "price": "100.50",
      "image_url": "http://localhost/storage/products/image.jpg",
      "created_at": "2026-06-05T10:30:00Z"
    },
    "relationships": {
      "category": {
        "data": { "id": "1", "type": "categories" }
      },
      "supplier": {
        "data": { "id": "1", "type": "suppliers" }
      }
    }
  }
}
```

### JSON:API Collection with Pagination
```json
{
  "data": [...],
  "links": {
    "first": "http://localhost/api/v1/products?page=1",
    "last": "http://localhost/api/v1/products?page=10",
    "prev": null,
    "next": "http://localhost/api/v1/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "path": "http://localhost/api/v1/products",
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

---

## Final Notes

- All timestamps are in ISO 8601 format (UTC)
- All monetary values are in BDT (Bangladeshi Taka)
- Pagination default: 15 items per page (10 for stock logs)
- Always show loading states during API calls
- Always show success/error messages via toast
- Always validate forms client-side before submitting
- Always handle API errors gracefully
- Implement debouncing for search inputs
- Cache API responses using React Query for better UX

---

**This specification covers 100% of the backend API functionality. Build each feature screen-by-screen following the client's requirements exactly.**