# E-Commerce Transformation Checklist

Transform your internal stock management system into a customer-facing e-commerce platform.

## 📋 Overview

This checklist transforms your stock management API into a full e-commerce REST API while maintaining all existing internal functionality.

### Key Implementation Notes:

1. **🔌 REST API Only**: Stateless API with token-based authentication (no sessions, no cookies)
2. **🎯 Auto-Seeding**: Role and Permission seeders automatically detect enum changes
3. **🛒 Guest Support**: Cart and checkout work for both authenticated and guest users via token-based identification
4. **🏪 Catalog vs Category**:
   - **Catalog** = Customer-facing product browsing API (public access to published products)
   - **Category** = Internal organizational structure (Electronics, Clothing, etc.)
   - Your existing Products module remains for staff; new `StorefrontController` serves customers

### Testing Strategy:
Tests are not included in this checklist. Implement testing separately if needed based on your QA process.

---

## Phase 1: Customer Authentication & Authorization

### 1.1 Customer Role & Permissions
- [ ] Add `Customer` role to `app/Enums/Role.php`
  - Value: `customer`
  - Label: `Customer`
  - Description: `Customer who can browse products and place orders`
  - Permissions needed:
    - View public product catalog
    - View own orders
    - Create orders for themselves
    - Cancel own orders (before processing)
    - Update own profile

- [ ] Add customer-specific permissions to `app/Enums/Permission.php`:
  - `CatalogView` - Browse public product catalog
  - `OrdersViewOwn` - View own orders
  - `OrdersCreateOwn` - Create orders for self
  - `OrdersCancelOwn` - Cancel own pending orders
  - `ProfileUpdate` - Update own profile

> **Note**: Seeders automatically detect enum changes - no manual seeder updates needed

### 1.2 Customer Registration
- [ ] Create `RegisterRequest` form request in `app/Http/Requests/Auth/`
  - Validate: name, email, password, password_confirmation
  - Optional: phone, address fields
  
- [ ] Add `register()` method to `AuthController`
  - Create user account
  - Auto-assign `Customer` role
  - Set `is_active` to true by default
  - Return token for immediate login
  - If `cart_token` provided in request, merge guest cart into new user account

- [ ] Add registration route: `POST /auth/register` (public)
  - Optional `cart_token` in body to merge guest cart

### 1.3 Guest Access
- [ ] Create middleware `EnsureCustomerOrGuest` to allow both authenticated customers and guests
- [ ] Update product routes to allow guest access (no auth required for browsing)
- [ ] Cart for guests will use token-based identification (cart token in header or as parameter)

---

## Phase 2: Public Product Display

> **Terminology**: "Catalog" refers to the customer-facing product browsing experience (all published products available for purchase). Your existing "Products" module remains for internal staff management. Categories organize products into groups (Electronics, Clothing, etc.).

### 2.1 Enhance Product Model for Public Display
- [ ] Add public visibility fields to products table migration:
  - `is_published` (boolean) - Whether product is visible to customers
  - `description` (text) - Full product description for customers
  - `short_description` (text) - Brief description for listings
  - `slug` (string, unique) - SEO-friendly URL
  - `meta_title` (string, nullable) - SEO meta title
  - `meta_description` (text, nullable) - SEO meta description
  - `display_order` (integer) - Sort order in listings

- [ ] Update `Product` model:
  - Add fillable fields
  - Add `published` scope for filtering published products
  - Add `sluggable` behavior (use `spatie/laravel-sluggable` or manual)
  - Add method to get public URL

- [ ] Add product variant support (optional for phase 2, critical for phase 3):
  - Create `product_variants` table (size, color, etc.)
  - SKU per variant
  - Price per variant
  - Stock per variant per warehouse

### 2.2 Category Enhancement
- [ ] Add category public fields:
  - `is_published` (boolean)
  - `slug` (string, unique)
  - `description` (text)
  - `image_url` (string) - Category banner/icon

- [ ] Update `Category` model with `published` scope

### 2.3 Product Images
- [ ] Update `Product` media collection to support multiple images:
  - Change from `singleFile()` to multiple files
  - Add `featured` flag to determine main image
  - Generate multiple conversions (thumb, medium, large)

- [ ] Add image alt text and title for accessibility/SEO

### 2.4 Public Product API (Customer-Facing)
- [ ] Create `StorefrontController` (or `PublicProductController`) with methods:
  - `index()` - List all published products with filters
  - `show($slug)` - Show single product by slug
  - `categoryProducts($categorySlug)` - Products by category
  - `featuredProducts()` - Featured/promoted products
  - `relatedProducts($productId)` - Related products

- [ ] Create `PublicProductResource` for customer display
  - Only expose customer-facing fields
  - Include: name, slug, price, images, category, stock availability (boolean, not quantity)
  - Exclude: supplier info, purchase price, warehouse details, internal notes

- [ ] Create `PublicCategoryResource` for customer category display

- [ ] Add filtering and sorting:
  - Filter by category, price range, availability
  - Sort by: newest, price (low-high, high-low), popularity
  - Pagination

- [ ] Add routes (all public, no auth required):
  - `GET /storefront/products` - All published products
  - `GET /storefront/products/{slug}` - Product detail
  - `GET /storefront/categories` - All published categories
  - `GET /storefront/categories/{slug}` - Category detail
  - `GET /storefront/categories/{slug}/products` - Category products
  - `GET /storefront/featured` - Featured products

---

## Phase 3: Shopping Cart

### 3.1 Cart Database Schema
- [ ] Create `carts` table migration:
  - `id`
  - `user_id` (nullable, for logged-in users)
  - `cart_token` (string, unique) - For guest users (UUID)
  - `expires_at`
  - `timestamps`
  - Index on `cart_token` and `user_id`

- [ ] Create `cart_items` table migration:
  - `id`
  - `cart_id`
  - `product_id` (or `product_variant_id` if using variants)
  - `warehouse_id` - Which warehouse to fulfill from
  - `quantity`
  - `price_snapshot` - Capture price at time of adding to cart
  - `timestamps`

### 3.2 Cart Model & Logic
- [ ] Create `Cart` model with relationships:
  - `belongsTo(User)` (nullable)
  - `hasMany(CartItem)`
  - Method: `total()` - Calculate cart total
  - Method: `itemCount()` - Total items
  - Method: `merge($otherCart)` - Merge guest cart with user cart on login
  - Method: `findOrCreateByToken($token)` - Get cart by token (for guests)
  - Method: `findOrCreateByUser(User $user)` - Get cart by user (for authenticated)

- [ ] Create `CartItem` model with relationships:
  - `belongsTo(Cart)`
  - `belongsTo(Product)`
  - `belongsTo(Warehouse)`
  - Method: `subtotal()` - quantity * price_snapshot

### Cart Identification Strategy (Stateless):
- **Guests**: Client generates/stores UUID, sends as `X-Cart-Token` header
- **Authenticated**: Use `user_id` from bearer token
- **Migration**: When guest logs in, API merges guest cart into user cart using provided cart token

### 3.3 Cart Service/Action
- [ ] Create `app/Actions/Cart/AddToCart.php`
  - Check stock availability
  - Check if product is published
  - Add or update quantity
  - Capture current price

- [ ] Create `app/Actions/Cart/RemoveFromCart.php`

- [ ] Create `app/Actions/Cart/UpdateCartItemQuantity.php`
  - Validate stock availability
  
- [ ] Create `app/Actions/Cart/ClearCart.php`

- [ ] Create `app/Actions/Cart/MergeGuestCart.php`
  - When guest logs in, merge their session cart with user cart

### 3.4 Cart API
- [ ] Create `CartController` with methods:
  - `show()` - Get current cart (requires `X-Cart-Token` header for guests)
  - `addItem(Request)` - Add product to cart
  - `updateItem($cartItemId, Request)` - Update quantity
  - `removeItem($cartItemId)` - Remove item
  - `clear()` - Clear entire cart

- [ ] Create `CartResource` and `CartItemResource`

- [ ] Add routes:
  - `GET /cart` - View cart (auth optional, requires X-Cart-Token for guests)
  - `POST /cart/items` - Add to cart (return cart_token on first add for guests)
  - `PATCH /cart/items/{cartItem}` - Update quantity
  - `DELETE /cart/items/{cartItem}` - Remove item
  - `DELETE /cart` - Clear cart

- [ ] Cart identification logic:
  - **Authenticated users**: Use `auth()->user()->id`
  - **Guest users**: Generate UUID cart token on first add, return in response, client sends via `X-Cart-Token` header

---

## Phase 4: Checkout & Order Placement

### 4.1 Customer Addresses
- [ ] Create `customer_addresses` table migration:
  - `id`
  - `user_id`
  - `type` (billing, shipping)
  - `is_default` (boolean)
  - `full_name`
  - `phone`
  - `address_line_1`
  - `address_line_2` (nullable)
  - `city`
  - `state`
  - `postal_code`
  - `country`
  - `timestamps`

- [ ] Create `CustomerAddress` model

- [ ] Create `CustomerAddressController`:
  - `index()` - List user's addresses
  - `store()` - Add new address
  - `update()` - Update address
  - `destroy()` - Delete address
  - `setDefault()` - Set as default

### 4.2 Update Sales Order for E-Commerce
- [ ] Update `sales_orders` table migration with customer fields:
  - `user_id` (nullable) - Link to registered customer
  - `customer_type` (guest, registered) - Track if guest or user
  - `order_access_token` (string, unique, nullable) - UUID for guest order access
  - `shipping_address_id` (nullable) - Link to saved address
  - `billing_address_id` (nullable)
  - `shipping_address_snapshot` (json) - Store full address at time of order
  - `billing_address_snapshot` (json)
  - `order_notes` (text, nullable) - Customer notes
  - `shipping_method` (string) - Delivery method
  - `shipping_cost` (decimal)
  - `tax_amount` (decimal)
  - `discount_amount` (decimal, default 0)
  - `coupon_code` (string, nullable)

- [ ] Update `SalesOrder` model:
  - Add relationships to User and CustomerAddress
  - Cast address snapshots as arrays
  - Method: `isCustomerOrder()` - Check if it's a customer order vs internal
  - Method: `canBeCancelledByCustomer()` - Only pending orders can be cancelled
  - Method: `generateAccessToken()` - Generate UUID token for guest access

### 4.3 Checkout Process
- [ ] Create `CheckoutController` with methods:
  - `show()` - Get checkout summary (cart + shipping + tax)
  - `calculateShipping(Request)` - Calculate shipping cost
  - `validateCart()` - Ensure all items still in stock and published
  - `placeOrder(Request)` - Convert cart to order

- [ ] Create `app/Actions/Checkout/CalculateOrderTotal.php`
  - Cart subtotal + shipping + tax - discount

- [ ] Create `app/Actions/Checkout/ValidateCheckout.php`
  - Stock availability check
  - Product still published
  - Price hasn't changed dramatically (optional warning)

- [ ] Create `app/Actions/Checkout/PlaceOrderFromCart.php`
  - Create SalesOrder from Cart
  - Create SalesOrderItems from CartItems
  - Snapshot addresses
  - Clear cart
  - Reserve stock (reduce warehouse_stock quantity)
  - Return order for payment initiation

- [ ] Add checkout routes:
  - `GET /checkout` - Checkout summary
  - `POST /checkout/shipping` - Calculate shipping
  - `POST /checkout/place-order` - Place order

### 4.4 Order Management for Customers
- [ ] Create `CustomerOrderController`:
  - `index()` - List customer's orders with filters
  - `show($orderId)` - View order detail
  - `cancel($orderId)` - Cancel pending order

- [ ] Update `SalesOrderPolicy`:
  - Allow customers to view only their own orders
  - Allow customers to cancel only their own pending orders

- [ ] Add routes:
  - `GET /orders` - My orders
  - `GET /orders/{salesOrder}` - Order detail
  - `POST /orders/{salesOrder}/cancel` - Cancel order

### 4.5 Guest Checkout
- [ ] Allow guest users to checkout without registration
  - Store customer info directly in sales_order fields
  - `customer_type` = 'guest'
  - Send order confirmation to email
  - Generate order access token for guest to view their order

- [ ] Create `GuestOrderController`:
  - `lookup(Request)` - Find order by email + order ID/token
  - `show($orderId, Request)` - View guest order (requires order_access_token)

- [ ] Guest order tracking:
  - Generate unique `order_access_token` (UUID) for each guest order
  - Guest uses email + order_access_token to view order
  - Token sent in order confirmation email

---

## Phase 5: Payment Integration

### 5.1 Payment Gateway Review
- [ ] Review existing SSLCommerz integration in:
  - `app/DTOs/SslcommerzPaymentPayload.php`
  - `app/Http/Controllers/PaymentCallbackController.php`
  - `app/Actions/SalesOrder/InitiateSalesOrderPayment.php`
  - `app/Actions/SalesOrder/FinalizeSalesOrderPayment.php`

- [ ] Ensure payment flow works for customer orders:
  - Generate payment URL after order placement
  - Handle success/fail/cancel callbacks
  - Update order status on successful payment
  - Release reserved stock on payment failure

### 5.2 Payment Methods
- [ ] Add support for multiple payment methods:
  - Online payment (SSLCommerz)
  - Cash on Delivery (COD) - Update order status to "Confirmed" immediately
  - Bank Transfer - Awaiting payment confirmation

- [ ] Add `payment_method` field to sales_orders table

- [ ] Create `PaymentMethod` enum with methods:
  - `Online`, `CashOnDelivery`, `BankTransfer`

### 5.3 Payment Status Tracking
- [ ] Ensure payment status enum includes:
  - `Pending` - Order placed, awaiting payment
  - `PaymentFailed` - Payment attempted but failed
  - `Paid` - Payment received
  - `Processing` - Order being prepared
  - `Shipped` - Order dispatched
  - `Delivered` - Order received by customer
  - `Cancelled` - Order cancelled
  - `Refunded` - Payment refunded

---

## Phase 6: Email Notifications

### 6.1 Customer Notifications
- [ ] Create email notifications for customers:
  - `OrderPlaced` - Order confirmation with details
  - `PaymentReceived` - Payment successful
  - `OrderShipped` - Shipping notification with tracking
  - `OrderDelivered` - Delivery confirmation
  - `OrderCancelled` - Cancellation confirmation
  - `OrderRefunded` - Refund processed

- [ ] Create Mailable classes in `app/Mail/`:
  - `OrderPlacedMail`
  - `PaymentReceivedMail`
  - `OrderShippedMail`
  - `OrderDeliveredMail`
  - `OrderCancelledMail`

- [ ] Trigger emails in appropriate actions:
  - After order placement
  - After payment confirmation
  - When order status changes

### 6.2 Email Templates
- [ ] Create Blade email templates in `resources/views/emails/orders/`:
  - `placed.blade.php`
  - `paid.blade.php`
  - `shipped.blade.php`
  - `delivered.blade.php`
  - `cancelled.blade.php`

- [ ] Include in email templates:
  - Order details (items, quantities, prices)
  - Shipping address
  - Payment summary
  - Order tracking link
  - Customer support contact

---

## Phase 7: Product Reviews & Ratings

### 7.1 Reviews Schema
- [ ] Create `product_reviews` table migration:
  - `id`
  - `product_id`
  - `user_id`
  - `sales_order_id` - Link to verified purchase
  - `rating` (1-5)
  - `title`
  - `review` (text)
  - `is_verified_purchase` (boolean)
  - `is_approved` (boolean) - Moderation flag
  - `helpful_count` (integer) - How many found it helpful
  - `timestamps`

- [ ] Create `review_helpfulness` table for tracking helpful votes:
  - `id`
  - `review_id`
  - `user_id`
  - `is_helpful` (boolean)
  - `timestamps`

### 7.2 Review Model & Logic
- [ ] Create `ProductReview` model:
  - Relationships: belongsTo Product, User, SalesOrder
  - Scope: `approved()` - Only show approved reviews
  - Scope: `verifiedPurchases()` - Only verified purchases

- [ ] Create `ReviewHelpfulness` model

- [ ] Update `Product` model:
  - `hasMany(ProductReview)`
  - Method: `averageRating()` - Calculate average
  - Method: `reviewCount()` - Total approved reviews

### 7.3 Review API
- [ ] Create `ProductReviewController`:
  - `index($productId)` - List product reviews
  - `store($productId, Request)` - Submit review
  - `update($reviewId, Request)` - Update own review
  - `destroy($reviewId)` - Delete own review
  - `markHelpful($reviewId)` - Mark review as helpful

- [ ] Create `ProductReviewResource`

- [ ] Add validation:
  - User must have purchased the product
  - One review per product per user
  - Rating 1-5 required

- [ ] Add routes:
  - `GET /products/{product}/reviews` - List reviews
  - `POST /products/{product}/reviews` - Submit review
  - `PATCH /reviews/{review}` - Update review
  - `DELETE /reviews/{review}` - Delete review
  - `POST /reviews/{review}/helpful` - Mark helpful

### 7.4 Admin Review Moderation
- [ ] Add permission: `ReviewsModerate`
- [ ] Create `AdminReviewController`:
  - `index()` - List all reviews (with filter for pending)
  - `approve($reviewId)` - Approve review
  - `reject($reviewId)` - Reject/hide review

---

## Phase 8: Wishlist

### 8.1 Wishlist Schema
- [ ] Create `wishlists` table migration:
  - `id`
  - `user_id`
  - `product_id`
  - `timestamps`
  - Unique constraint on (user_id, product_id)

### 8.2 Wishlist Model & API
- [ ] Create `Wishlist` model

- [ ] Create `WishlistController`:
  - `index()` - Get user's wishlist
  - `store(Request)` - Add to wishlist
  - `destroy($productId)` - Remove from wishlist

- [ ] Add routes:
  - `GET /wishlist` - My wishlist
  - `POST /wishlist` - Add to wishlist
  - `DELETE /wishlist/{product}` - Remove from wishlist

---

## Phase 9: Enhanced Search & Filtering

### 9.1 Enhance Product Search for Customers
- [ ] Update Scout configuration for customer product search:
  - Index: name, description, SKU, category name
  - Ensure only published products in results
  - Add to `StorefrontController`

- [ ] Add search route:
  - `GET /storefront/search?q={query}` - Search published products
  - Include filters: category, price range, rating, availability

### 9.2 Faceted Search
- [ ] Implement filter aggregations:
  - Categories (with counts)
  - Price ranges
  - Ratings (4+ stars, 3+ stars, etc.)
  - Availability (in stock, out of stock)

---

## Phase 10: Promotions & Discounts

### 10.1 Coupon System
- [ ] Create `coupons` table migration:
  - `id`
  - `code` (unique)
  - `description`
  - `type` (percentage, fixed_amount, free_shipping)
  - `value` (discount value)
  - `min_purchase_amount` (nullable)
  - `max_discount_amount` (nullable) - For percentage coupons
  - `usage_limit` (nullable) - Total times can be used
  - `usage_per_user` (nullable)
  - `valid_from`
  - `valid_until`
  - `is_active`
  - `timestamps`

- [ ] Create `coupon_usages` table to track usage:
  - `id`
  - `coupon_id`
  - `user_id` (nullable)
  - `sales_order_id`
  - `discount_amount`
  - `timestamps`

### 10.2 Coupon Model & Logic
- [ ] Create `Coupon` model:
  - Scope: `active()`
  - Method: `isValid()` - Check dates and usage limits
  - Method: `calculateDiscount($cartTotal)`

- [ ] Create `CouponUsage` model

- [ ] Create `app/Actions/Coupon/ApplyCoupon.php`
  - Validate coupon
  - Calculate discount
  - Check usage limits

- [ ] Update checkout to apply coupon discounts

### 10.3 Coupon API
- [ ] Add to `CheckoutController`:
  - `applyCoupon(Request)` - Apply coupon to cart
  - `removeCoupon()` - Remove coupon

---

## Phase 11: Customer Dashboard

### 11.1 Customer Profile
- [ ] Create `CustomerProfileController`:
  - `show()` - Get customer profile
  - `update(Request)` - Update profile info
  - `changePassword(Request)` - Change password

- [ ] Add routes:
  - `GET /customer/profile` - Get profile
  - `PATCH /customer/profile` - Update profile
  - `POST /customer/change-password` - Change password

### 11.2 Order History
- [ ] Enhance order listing for customers:
  - Filter by status
  - Search by order ID
  - Date range filter
  - Export orders (CSV/PDF)

---

## Phase 12: Analytics & Reporting

### 12.1 Admin E-Commerce Reports
- [ ] Add to dashboard metrics:
  - Total sales (today, this week, this month)
  - Order count by status
  - Top selling products
  - Revenue by category
  - Customer acquisition metrics
  - Average order value

- [ ] Create dedicated reports:
  - Sales report by date range
  - Product performance report
  - Customer order frequency report
  - Abandoned cart report

### 12.2 Customer Insights
- [ ] Track customer behavior:
  - Last login
  - Total orders
  - Total spent
  - Average order value
  - Favorite categories

---

## Phase 13: SEO & Performance

### 13.1 SEO Optimization
- [ ] Add meta tags to products and categories:
  - Title, description, keywords
  - Open Graph tags for social sharing
  - Structured data (JSON-LD) for rich snippets

- [ ] Create `sitemap.xml` generator:
  - Include all published products
  - Include all published categories
  - Update frequency indicators

- [ ] Implement canonical URLs

- [ ] Create `robots.txt`

### 13.2 Performance Optimization
- [ ] Enable query result caching for catalog:
  - Cache product listings
  - Cache category trees
  - Cache featured products

- [ ] Optimize images:
  - Lazy loading
  - WebP format
  - Responsive images (srcset)

- [ ] Implement CDN for static assets

- [ ] Add Redis caching for:
  - Product catalog
  - Categories
  - Cart data
  - Session data

---

## Phase 14: Mobile API Optimization

### 14.1 API Versioning
- [ ] Ensure API versioning is in place (`/api/v1`)
- [ ] Document all customer-facing endpoints
- [ ] Use Scramble or L5-Swagger for API documentation

### 14.2 Response Optimization
- [ ] Implement sparse fieldsets (only return requested fields)
- [ ] Add response compression
- [ ] Optimize image URLs in responses

---

## Phase 15: Security & Compliance

### 15.1 Security Hardening
- [ ] Implement rate limiting on:
  - Registration endpoint
  - Login endpoint
  - Cart operations
  - Order placement

- [ ] Add CAPTCHA on registration/login (optional)

- [ ] Sanitize all user input

- [ ] Add SQL injection prevention (already handled by Eloquent)

- [ ] Prevent XSS attacks in reviews and user input

### 15.2 Privacy & Compliance
- [ ] Add privacy policy page
- [ ] Add terms & conditions page
- [ ] Implement GDPR compliance:
  - Data export functionality
  - Data deletion functionality
  - Cookie consent

- [ ] Add PCI DSS compliance considerations for payment data
  - Never store card details
  - Use tokenization via payment gateway

---

## Phase 16: Deployment & DevOps

### 16.1 Environment Setup
- [ ] Separate customer-facing frontend from admin panel
- [ ] Configure separate domains/subdomains if needed:
  - `shop.example.com` - Customer store
  - `admin.example.com` - Admin panel

### 16.2 Performance Monitoring
- [ ] Set up error tracking (Sentry, Bugsnag)
- [ ] Set up performance monitoring (New Relic, Scout APM)
- [ ] Set up uptime monitoring

### 16.3 Backup & Recovery
- [ ] Automate database backups
- [ ] Backup media files
- [ ] Test restore procedures

---

## Phase 17: Documentation

### 17.1 API Documentation
- [ ] Document all customer-facing API endpoints
- [ ] Provide example requests/responses
- [ ] Document authentication flow
- [ ] Document error codes and messages

### 17.2 Admin Documentation
- [ ] Document how to manage products for e-commerce
- [ ] Document order fulfillment workflow
- [ ] Document coupon management
- [ ] Document review moderation

---

## Implementation Priority

### 🔴 **Critical (MVP)** - Must have for launch
1. Customer authentication (registration, login)
2. Public product catalog (list, detail, search)
3. Shopping cart (add, update, remove)
4. Checkout & order placement
5. Payment integration (at least one method)
6. Order confirmation emails
7. Customer order history

### 🟡 **High Priority** - Shortly after MVP
1. Guest checkout
2. Customer addresses management
3. Product images (multiple)
4. Product reviews & ratings
5. Order status tracking
6. Promotions & coupons
7. Wishlist

### 🟢 **Medium Priority** - Enhancements
1. Advanced search & filtering
2. Customer dashboard enhancements
3. Email marketing integration
4. Analytics & reporting
5. SEO optimization
6. Mobile app API

### ⚪ **Low Priority** - Nice to have
1. Social media integration
2. Live chat support
3. Product recommendations engine
4. Loyalty program
5. Multi-currency support
6. Multi-language support

---

## Notes

- **Backward Compatibility**: Ensure all existing internal staff functionality remains intact. Use role-based access control to separate customer features from staff features.

- **Stock Management**: Consider which warehouse(s) will fulfill customer orders. You may want:
  - A dedicated "retail" warehouse
  - Automatic warehouse selection based on stock availability
  - Regional warehouse selection based on shipping address

- **Testing Environment**: Set up a staging environment to test customer-facing features without affecting internal operations.

- **Gradual Rollout**: Consider launching in private beta first, then public launch.

- **Customer Support**: Plan for customer support channels (email, phone, live chat).

- **Returns & Refunds**: Plan the workflow for handling returns, refunds, and exchanges (Phase 19 - optional).

---

## Estimated Timeline

- **Phase 1-4** (MVP): 2-3 weeks
- **Phase 5-7**: 2 weeks
- **Phase 8-12**: 2-3 weeks
- **Phase 13-17**: 1-3 weeks (ongoing)

**Total Estimated Time**: 7-11 weeks for full implementation

---

## Next Steps

1. Review and prioritize features based on your business needs
2. Start with Phase 1 (Customer Authentication)
3. Set up development and staging environments
4. Create a dedicated git branch for e-commerce features
5. Begin implementation following this checklist
