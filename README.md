# StockMate — Inventory Management System

A production-grade inventory management REST API built with Laravel 13, demonstrating advanced backend engineering, modern PHP patterns, and real-world business logic. Designed for small to mid-size trading businesses managing products, stock, warehouses, suppliers, purchase orders, and sales — including online payment via SSLCommerz.

**📘 For recruiters and technical deep-dive:** See [FEATURES.md](FEATURES.md) for architecture, design patterns, and technical sophistication.

This is a backend-focused project. A companion React frontend is available for visual demonstration. Both are live and running.

---

## Live Demo
![Inventory Management System](stockmate.png)
| | URL |
|---|---|
| **Frontend** | [stockmate-demo.vercel.app](https://stockmate-demo.vercel.app) |
| **Backend API** | [stockmate-pq66.onrender.com](https://stockmate-pq66.onrender.com) |
| **API Docs** | [stockmate-pq66.onrender.com/docs/api](https://stockmate-pq66.onrender.com/docs/api) |

> The database is pre-seeded with realistic dummy data — products, warehouses,
> suppliers, purchase orders, sales, and stock history — so it feels like an
> already running business, not an empty demo.

**Demo credentials:**

| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | password |
| Staff | staff@example.com | password |

---

## Tech Stack

### Core Framework & Language
- **PHP** — 8.4 with strict types, property promotion, enums
- **Laravel** — 13.8 (latest features including type-hinted models)
- **Laravel Octane** — Ready for high-performance deployment (Swoole/FrankenPHP/RoadRunner)

### Authentication & Authorization
- **Laravel Sanctum** — Stateless API token authentication
- **Laravel Socialite** — OAuth integration (Google, Microsoft)
- **Spatie Permission** — Role-based access control
- **Spatie One-Time Passwords** — Email verification and password reset

### Database & Caching
- **Database** — MySQL/PostgreSQL with comprehensive migrations
- **Redis** — Multi-layer caching with tag-based invalidation
- **Laravel Scout** — Full-text search (database driver, Meilisearch/Algolia ready)

### Third-Party Integrations
- **SSLCommerz** — Bangladesh payment gateway with transaction verification
- **Twilio** — SMS notifications (configured for future low-stock alerts)
- **Google API Client** — OAuth token verification
- **Maatwebsite Excel** — Excel/CSV export functionality

### API & Documentation
- **Scramble** — Auto-generated OpenAPI documentation
- **Spatie Query Builder** — Declarative API filtering, sorting, includes
- **API Resources** — Structured JSON transformations

### File Handling
- **Spatie Media Library** — File uploads with image transformations
- **Laravel Storage** — S3-compatible file storage

### Logging & Monitoring
- **Spatie Activity Log** — Comprehensive audit trail
- **Laravel Pail** — Real-time log streaming
- **Sentry** — Error tracking (configured)

### Testing & Quality
- **Pest** — 4.7 with 80+ feature tests
- **Spectator** — OpenAPI contract testing
- **Laravel Pint** — Opinionated code formatter
- **Larastan** — Static analysis (PHPStan for Laravel)

### Frontend Build
- **Vite** — 8.0 for asset bundling
- **Tailwind CSS** — 4.0 for API documentation UI

---

## Features

### Authentication & Authorization
- Token-based API authentication via Sanctum with ability-based scopes
- OAuth social login (Google, Microsoft) with token verification
- OTP-based email verification and password reset
- Role-based access control (Admin/Staff) with 11 policy classes
- Account activation system — new users must be activated by admin
- Comprehensive audit logging of all user actions

### Product Management
- Full CRUD with SKU, pricing, categories, and suppliers
- Image uploads with automatic transformations via Spatie Media Library
- Policy-based authorization for all operations
- Global search across products, suppliers, and warehouses
- Low stock alerts with configurable reorder thresholds

### Multi-Warehouse Inventory
- Stock tracked **per product per warehouse**, not globally
- Inter-warehouse stock transfers with atomic transactions
- Stock in, stock out, adjustment operations
- Immutable audit trail — every movement logged with user, timestamp, and note
- Real-time stock quantity updates with observer-based cache invalidation
- Negative stock prevention at database level

### Purchase Orders
- Full lifecycle: `draft` → `ordered` → `partially_received` → `received` → `cancelled`
- Partial receiving supported — multiple receipts per order
- Stock only updates on receive, never on order creation
- Cannot cancel orders with already-received items
- Automatic status calculation from received vs ordered quantities

### Sales Orders + Payment Integration
- Sales orders with line items and customer details
- SSLCommerz payment gateway integration with full security:
  - Transaction ID verification (prevents replay attacks)
  - Server-side amount validation
  - Multiple API calls to verify payment authenticity
  - Idempotent callback handling
- Stock only decrements on confirmed, verified payment
- Full payment payload stored for compliance and audit
- Payment states: `Pending` → `Initiated` → `Paid`/`Failed`/`Cancelled`

### Reports & Analytics
- Stock summary per product with low stock indicators
- Stock movement report by date range with Excel/CSV export
- Low stock report across all warehouses
- Purchase order reports by supplier and date
- Sales reports with payment status filtering
- Activity log with user and date range filtering (admin only)

### Event-Driven Notifications
- Low stock email alerts when quantity drops below reorder threshold
- Out of stock notifications to all admins
- SMS/WhatsApp support via Twilio (configured, ready to enable)
- Email verification and password reset via OTP

### Performance & Caching
- Redis caching reduces database queries by up to 85%
- Multi-layer cache strategy:
  - Dashboard metrics: 5-minute TTL with lock-based stampede prevention
  - Reference data: 1-hour TTL
  - Listings: Flexible TTL with stale-while-revalidate
  - Historical reports: 24-hour TTL
- Observer-based cache invalidation on model changes
- Tag-based grouped cache invalidation
- Laravel Octane ready for high-performance deployment

---

## Architecture & Code Quality

### Design Patterns
- **Action Classes** — Business logic encapsulated in dedicated classes (20+ actions)
- **Data Transfer Objects** — Type-safe, immutable data structures with readonly properties
- **Repository Pattern** — Clean separation between business logic and data access
- **Policy-Based Authorization** — 11 policy classes for fine-grained access control
- **Observer Pattern** — Cache invalidation and event dispatching via model observers
- **Custom Query Filters** — Reusable filter classes for complex API queries

### Database Design
- **18 Eloquent Models** organized by domain (Auth, Product, Inventory, Orders, Audit)
- **Transaction Safety** — All critical operations wrapped in database transactions
- **Enum-Driven State Machines** — Type-safe status management with backed enums
- **Comprehensive Relationships** — 1:N, N:M, polymorphic, with eager loading
- **Audit Trail** — Immutable stock logs and activity logs for compliance

### Testing
- **80+ Pest Tests** covering all API endpoints
- **OpenAPI Contract Testing** via Spectator — every request/response validated
- **Factory-Based Test Data** — Realistic, reusable test fixtures
- **Policy Testing** — Authorization rules verified at test level
- **Strategic Mocking** — External services mocked (SSLCommerz, OAuth)
- **Time-Based Testing** — Carbon travel() for OTP expiry scenarios

### Code Quality Tools
- **Laravel Pint** — Automatic code formatting
- **Larastan** — Static analysis at level 5
- **PHP 8.4** — Strict types, property promotion, match expressions
- **Type Safety** — Explicit return types and parameter types throughout

---

## API Documentation

This project uses [Scramble](https://scramble.dedoc.co/) for automatic API
documentation. All endpoints, request bodies, response shapes, and authentication
requirements are documented and always in sync with the code.

**Live docs:** [stockmate-pq66.onrender.com/docs/api](https://stockmate-pq66.onrender.com/docs/api/)

Alternatively, the [frontend](https://stockmate-demo.vercel.app) covers the core flows
visually if you prefer to explore that way.

---

## Frontend

**[stockmate-demo.vercel.app](https://stockmate-demo.vercel.app)**
— React, connects to the live API

> **Note:** The frontend is fully vibe coded and covers approximately 50% of the API's capabilities and
> exists purely for visual demonstration. Core flows — login, dashboard, product
> management, stock in/out, and purchase orders — are functional. Advanced features
> such as filtering, report exports, stock transfers, and sales order payment flow
> are best explored through the API docs.
>
> This is a backend-focused project. The frontend is there to show the system
> working, not to be a complete production UI.

Frontend source: [github.com/HasanAshab/stockmate-frontend](https://github.com/HasanAshab/stockmate-frontend)

---

## Local Setup

### Requirements

- PHP 8.4+
- Composer 2.x
- MySQL 8+ or PostgreSQL 15+
- Redis 7+
- Node.js 18+ (for asset compilation)

### Steps

**1. Clone the repository**

```bash
git clone https://github.com/HasanAshab/stockmate.git
cd stockmate
```

**2. Install dependencies**

```bash
composer install
```

**3. Environment setup**

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure:

```env
# Database
DB_CONNECTION=mysql
DB_DATABASE=stockmate
DB_USERNAME=root
DB_PASSWORD=

# Cache — Redis
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

# Queue
QUEUE_CONNECTION=database

# Mail (Mailtrap for local testing, or use log driver)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS=admin@stockmate.test

# SSLCommerz (get credentials from sslcommerz.com)
SSLC_SANDBOX=true
SSLC_STORE_CURRENCY=BDT
SSLC_STORE_ID=your_store_id
SSLC_STORE_PASSWORD=your_store_password

# Google OAuth (optional, for social login)
GOOGLE_CLIENT_ID=your_google_client_id

# Twilio (optional, for SMS notifications)
TWILIO_ACCOUNT_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_token
TWILIO_FROM=your_twilio_number
```

**4. Run migrations and seed**

```bash
php artisan migrate --seed
```

Seeds the database with realistic dummy data across all modules — products,
warehouses, suppliers, stock history, purchase orders, and sales orders.
Includes one admin and one staff account ready to use.

**5. Storage link**

```bash
php artisan storage:link
```

**6. Start the server**

```bash
php artisan serve
```

Or for full development experience with queue worker and Vite:

```bash
composer run dev
```

This runs server + queue worker + Vite concurrently.

**Access:**
- API: `http://localhost:8000/api`
- API Docs: `http://localhost:8000/docs/api`

**Run tests:**

```bash
composer test -- --compact
```

---

## Default Accounts (After Seeding)

| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | password |
| Staff | staff@example.com | password |

---

## Database Design

### Core Tables

| Table | Purpose | Relationships |
|---|---|---|
| `users` | Admin and staff accounts with role and active status | → `activity_log`, `stock_logs` |
| `categories` | Product categories | ← `products` |
| `suppliers` | Supplier details linked to products | ← `products`, `purchase_orders` |
| `products` | Product catalog with SKU, pricing, image, reorder levels | → `warehouse_stock`, `stock_logs`, `order_items` |
| `warehouses` | Physical warehouse locations | → `warehouse_stock`, `stock_logs` |
| `warehouse_stock` | Stock quantity per product per warehouse (pivot) | ← `products`, `warehouses` |
| `stock_logs` | Immutable history of every stock movement | ← `products`, `warehouses`, `users` |
| `purchase_orders` | Orders raised to suppliers with status tracking | → `purchase_order_items` |
| `purchase_order_items` | Line items per PO with received quantity tracking | ← `purchase_orders`, `products` |
| `sales_orders` | Customer sales with payment status | → `sales_order_items` |
| `sales_order_items` | Line items per sales order | ← `sales_orders`, `products` |
| `activity_log` | System-wide audit trail (Spatie) | Polymorphic to all models |
| `personal_access_tokens` | Sanctum API tokens | ← `users` |

### Key Design Decisions

1. **Stock per Warehouse** — `warehouse_stock` is the single source of truth for inventory quantities
2. **Immutable Audit Trail** — `stock_logs` table never updated or deleted, only inserted
3. **Partial Receiving** — `purchase_order_items.received_quantity` tracks incremental receipts
4. **Payment State Tracking** — `sales_orders.payment_status` and `payment_data` JSON column
5. **Soft Deletes** — Most models use soft deletes for data retention and audit compliance

---

## Coming Soon

These features are planned for a future version:

- **Login with Phone or Email** — Support both Phone number and Email to login
- **SMS / WhatsApp Notifications** — Low stock and order alerts via SMS alongside
  email, using a Bangladesh-compatible gateway
- **Barcode Label Printing** — Generate and print barcodes directly from the
  product management panel
- **Customer Portal** — A separate login for customers to track their own sales
  orders and payment history
- **Advanced Analytics** — Charts and trends for stock movement, top-selling
  products, supplier performance, and warehouse utilization over time
- **Multi-Currency Support** — Record purchase costs and sales prices in multiple
  currencies with a base currency conversion
- **Custom Role Builder** — Let admins define granular permissions per user instead
  of fixed admin/staff roles
- **Mobile App** — The API is already mobile-ready with full Sanctum token support.
  A React Native or Flutter app is the natural next step.
- **Audit Log Export** — Export the full activity log as CSV or PDF for compliance
  and accounting purposes

### AI-Powered Features

* **Demand Forecasting** — Predict future stock demand using historical sales,
  seasonal trends, and warehouse movement patterns

* **Smart Reorder Recommendations** — Automatically suggest purchase orders based
  on low stock thresholds, sales velocity, and supplier lead times

* **Purchase Insights** — Detect unusually expensive supplier pricing and suggest
  better purchasing decisions from historical data

* **Natural Language Dashboard Queries** — Ask questions like:
  *“Which products are running out fastest?”* or
  *“Show warehouses with dead stock older than 90 days”*

* **Sales Anomaly Detection** — Detect suspicious sales spikes, duplicate orders,
  or abnormal warehouse stock movements automatically

* **Invoice & Receipt OCR** — Upload supplier invoices or receipts and automatically
  extract products, quantities, prices, and totals into purchase orders

* **Product Auto-Categorization** — Automatically categorize products from title,
  description, or supplier data

* **Supplier Performance Scoring** — Evaluate suppliers based on delivery speed,
  stock quality, pricing consistency, and cancellation rate

* **Predictive Low Stock Alerts** — Instead of alerting only after stock becomes low,
  forecast when inventory will likely run out

* **Assistant for Admins** — An internal assistant capable of summarizing reports,
  generating operational insights, and helping staff navigate the system faster

---

## Project Metrics

| Metric | Count |
|---|---|
| **API Endpoints** | 60+ |
| **Eloquent Models** | 18 |
| **Policies** | 11 |
| **Action Classes** | 20+ |
| **Feature Tests** | 80+ |
| **Form Requests** | 25+ |
| **API Resources** | 14 |
| **Database Tables** | 18 |
| **Enum Types** | 8 |
| **Custom Filters** | 3 |
| **Event Listeners** | 4 |
| **Model Observers** | 6 |

---

## Project Background

This project was built as a portfolio piece to demonstrate backend development skills in Laravel. The scope was chosen to reflect real-world software that businesses in Dhaka's trading sector actually use — inventory trackers, supplier management tools, and payment-integrated sales systems.

**Development Priority:**
1. Core stock management with multi-warehouse support
2. Transaction safety and data integrity
3. Purchase and sales order workflows
4. Payment integration with verification
5. Performance optimization via caching
6. Comprehensive testing and documentation

**Key Focus Areas:**
- Production-minded code (transactions, validation, audit trails)
- Stock can never go negative (database constraints + application logic)
- Every critical operation is fully auditable
- Frequently-hit endpoints cached to reduce database load by up to 85%
- 80+ tests ensure refactoring confidence

**For Technical Deep-Dive:** See [FEATURES.md](FEATURES.md) for architecture details, design patterns, and code examples.

---

## License

MIT