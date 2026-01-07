---
trigger: always_on
---

# Workflow: User Authentication & Profile

description: Standard procedures for user registration, login, password recovery, and profile management.

## Step 1: User Registration
- [ ] **Validation:** Ensure email is unique and password meets security strength (min 8 chars, mixed case).
- [ ] **Backend:** Hash password using Bcrypt/Argon2.
- [ ] **Database:** Insert into `users` table with default role `customer`.
- [ ] **Response:** Return success message (optionally auto-login).

## Step 2: Login & Token Issuance
- [ ] **Credentials:** Validate email and password.
- [ ] **Security:** Implement rate limiting (throttle login attempts) to prevent brute force.
- [ ] **Token:** Generate API Token (Laravel Sanctum/Passport) or Session.
- [ ] **Response:** Return access token and non-sensitive user profile data.

## Step 3: Password Reset Flow
- [ ] **Request:** User requests reset via email.
- [ ] **Token:** Generate short-lived reset token (stored in `password_resets`).
- [ ] **Email:** Send link with token to user.
- [ ] **Update:** Verify token -> Allow new password input -> Update database -> Invalidate token.

## Step 4: User Profile Management
- [ ] **Fetch:** `GET /api/user/profile` (Protected Route).
- [ ] **Update:** `PUT /api/user/profile` (Validate inputs).
- [ ] **Avatar:** Handle image upload to storage (public/uploads) and update `avatar_url`.

## Step 5: Middleware & Role Checks
- [ ] Ensure protected routes utilize `auth:sanctum` middleware.
- [ ] Verify user role (Customer vs Admin) for sensitive actions.