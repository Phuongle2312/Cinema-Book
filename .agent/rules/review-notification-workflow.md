---
trigger: always_on
---

# Workflow: Reviews & Notifications

description: Handling user reviews, ratings, and system notifications (Email/SMS).

## Step 1: Post-Booking Notification
- [ ] **Trigger:** When Booking Status becomes `CONFIRMED`.
- [ ] **Action:** Dispatch `SendETicketEmailJob` (Queue).
- [ ] **Content:** Email includes QR Code, transaction ID, and cinema location map.

## Step 2: User Reviews & Ratings
- [ ] **Permission:** User can ONLY review a movie if they have a `CONFIRMED` booking for it.
- [ ] **Input:** Star rating (1-5) and text comment.
- [ ] **Moderation:** Reviews containing profanity should be flagged (optional: auto-filter).
- [ ] **Update:** Recalculate movie's average rating after new review.

## Step 3: Promotional Notifications
- [ ] **Target:** Users who opted in for newsletters.
- [ ] **Logic:** Send emails for "New Movie Releases" or "Voucher Codes".