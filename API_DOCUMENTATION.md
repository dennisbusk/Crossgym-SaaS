# CrossGym SaaS API Documentation

## Overview
This API provides full CRUD access to all models in the CrossGym SaaS application. It is secured using JWT (JSON Web Tokens) and versioned under `/api/v1`.

**Base URL:** `https://your-domain.com/api`

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
- `GET /api/v1/<resource>` - List (paginated)
- `POST /api/v1/<resource>` - Create
- `GET /api/v1/<resource>/{id}` - Show
- `PUT/PATCH /api/v1/<resource>/{id}` - Update
- `DELETE /api/v1/<resource>/{id}` - Delete

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

## Postman Collection
A full Postman collection is provided in `postman_collection.json`.
