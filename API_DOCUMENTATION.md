# CrossGym SaaS API Documentation

## Overview
This API provides full CRUD access to all models in the CrossGym SaaS application. It is secured using JWT (JSON Web Tokens) and versioned under `/api/v1`.

**Base URL:** `https://your-domain.com/api`

---

## Route Names (for Laravel Developers)
All API routes are named with the `api.` prefix. This is useful when using the `route()` helper in Laravel:

- **Authentication:** `api.login`, `api.logout`, `api.refresh`, `api.me`
- **V1 Resources:** `api.<resource>.<action>` (e.g., `api.users.index`, `api.gym-classes.show`, `api.gym-classes.book`, `api.gym-classes.cancel-booking`, `api.gym-classes.waitlist`, `api.gym-classes.wod`, `api.dashboard.hero`, `api.dashboard.activity-feed`, `api.dashboard.activity-feed.react`, `api.tenants.occupancy`, `api.users.stats`, `api.users.attendance`, `api.users.achievements`, `api.user.recovery`, `api.ai.suggestions`, `api.challenges.index`, `api.membership.wallet-pass`, `api.user.devices.sync`, `api.support.faqs`, `api.support.tickets`)
- **Stripe:** `api.stripe.portal`, `api.stripe.checkout`

---

## Authentication
Authentication is handled via JWT. All requests to protected routes must include the `Authorization: Bearer <token>` header.

### Login
- **URL:** `/api/auth/login`
- **Method:** `POST`
- **Body:**
  ```json
  {
    "email": "user@example.com",
    "password": "password"
  }
  ```
- **Response:**
  ```json
  {
    "access_token": "...",
    "token_type": "bearer",
    "expires_in": 3600
  }
  ```

### Logout
- **URL:** `/api/auth/logout`
- **Method:** `POST`
- **Header:** `Authorization: Bearer <token>`

### Refresh Token
- **URL:** `/api/auth/refresh`
- **Method:** `POST`
- **Header:** `Authorization: Bearer <token>`

---

## API V1 Resources (Full CRUD)
All resources support the following standard operations:
- `GET /api/v1/<resource>` - List (non-paginated, supports filtering)
- `POST /api/v1/<resource>` - Create
- `GET /api/v1/<resource>/{id}` - Show
- `PUT/PATCH /api/v1/<resource>/{id}` - Update
- `DELETE /api/v1/<resource>/{id}` - Delete

### Filtering and Search
Most `GET /api/v1/<resource>` endpoints support the following query parameters:
- `tenant_id`: Filter by tenant ID.
- `search`: Search by name or other relevant text fields.
- `from_date` / `to_date`: Filter by date range (supported on `gym-classes`, `payments`, `workout-logs`, `check-ins`).

### Example: Gym Classes
`GET /api/v1/gym-classes?tenant_id=1&from_date=2026-05-01&to_date=2026-05-31&search=Crossfit`

### Available Resources
- `users`
- `tenants`
- `roles`
- `gym-classes`
- `class-types`
- `exercises`
- `workout-logs`
- `check-ins`
- `payments`
- `plans`
- `subscriptions`
- `email-templates`
- `system-settings`
- `ai-coach-settings`
- `calendars`
- `colors`
- `dashboards`
- `email-logs`
- `gym-class-trials`
- `permissions`
- `processed-stripe-events`
- `retentions`
- `stripe-webhook-logs`
- `subscription-options`
- `super-admins`
- `user-dashboard-widgets`

---

## Stripe Integration (Mobile & Web)
Special endpoints for generating Stripe Hosted Session URLs.

### Get Portal URL
Generate a URL for the Stripe Customer Portal.
- **URL:** `/api/v1/stripe/portal`
- **Method:** `POST`
- **Body:**
  ```json
  {
    "return_url": "crossgym://home"
  }
  ```
- **Response:**
  ```json
  {
    "url": "https://billing.stripe.com/p/session/..."
  }
  ```

### Get Checkout URL
Generate a URL for Stripe Hosted Checkout.
- **URL:** `/api/v1/stripe/checkout`
- **Method:** `POST`
- **Body:**
  ```json
  {
    "price_id": "price_...",
    "mode": "subscription",
    "success_url": "crossgym://checkout-success",
    "cancel_url": "crossgym://checkout-cancel"
  }
  ```
- **Response:**
  ```json
  {
    "url": "https://checkout.stripe.com/c/session/..."
  }
  ```

---

## Mobile App Specific Endpoints (v1)

These endpoints are optimized for the mobile experience.

### Booking System
- `POST /api/v1/gym-classes/{id}/book` - Reserve a spot.
- `DELETE /api/v1/gym-classes/{id}/book` - Cancel reservation.
- `POST /api/v1/gym-classes/{id}/waitlist` - Join waitlist for full class.
- `GET /api/v1/gym-classes/{id}/wod` - Reveal workout details.

### Dashboard & Social
- `GET /api/v1/dashboard/hero` - Get most relevant next action for user.
- `GET /api/v1/dashboard/activity-feed` - Recent gym activities.
- `GET /api/v1/tenants/{id}/occupancy` - Real-time occupancy percentage.

### User Progress & Stats
- `GET /api/v1/users/me/stats` - Aggregated user stats (streak, total workouts).
- `GET /api/v1/users/me/attendance?months=6` - Attendance history data.
- `GET /api/v1/users/me/achievements` - Unlocked achievements.

### Support
- `GET /api/v1/support/faqs` - Frequently asked questions.
- `POST /api/v1/support/tickets` - Submit a support ticket.

### Integrations
- `GET /api/v1/membership/wallet-pass` - Returns Apple Wallet pass file.
- `POST /api/v1/user/devices/sync` - Sync health data (steps, calories).

---

## Postman Collection
A full Postman collection is provided in `postman_collection.json`.
