---
trigger: always_on
---

# Workflow: Movie Discovery & Homepage

description: Logic for displaying movie lists, filtering, and search functionality on the frontend.

## Step 1: Homepage Data Aggregation
- [ ] **Endpoint:** `GET /api/movies/homepage` (Optimized query).
- [ ] **Sections:**
    - **Now Showing:** Movies with `status = 'showing'`.
    - **Coming Soon:** Movies with `status = 'coming_soon'`.
    - **Top Rated/Trending:** Based on booking count or review scores.

## Step 2: Search & Filter Logic
- [ ] **Search:** Implement fuzzy search by title (e.g., `LIKE %query%`).
- [ ] **Filters:** Allow filtering by `Genre`, `Language`, `Date`.
- [ ] **Debounce:** Frontend must debounce search input (300-500ms) to reduce API calls.

## Step 3: Movie Details Page
- [ ] **Endpoint:** `GET /api/movies/{id}`.
- [ ] **Related Data:** Include `genres`, `cast`, `trailer_url`, and `reviews` (paginated).
- [ ] **SEO:** Ensure meta tags (Open Graph) are populated for social sharing.