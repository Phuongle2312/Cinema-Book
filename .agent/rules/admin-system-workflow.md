---
trigger: always_on
---

# Workflow: Admin System Management

description: Procedures for administrators to manage movies, showtimes, theaters, and view reports.

## Step 1: Movie Management (CRUD)
- [ ] **Create/Update:** Admin inputs movie details (title, cast, duration, poster).
- [ ] **Validation:** Ensure `release_date` is valid.
- [ ] **Status:** Toggle movie status (`Coming Soon`, `Now Showing`, `Ended`).

## Step 2: Showtime Scheduling (Complex Logic)
- [ ] **Input:** Select Movie -> Select Theater/Room -> Select Date -> Input Time.
- [ ] **Conflict Check (Crucial):**
    - [ ] Calculate `end_time` = `start_time` + `movie_duration` + `cleaning_time` (e.g., 15 mins).
    - [ ] Query existing showtimes in the SAME room on the SAME date.
    - [ ] **Rule:** New showtime range must NOT overlap with any existing showtime range.
- [ ] **Error:** If overlap detected, return specific error: "Room is occupied from X to Y".

## Step 3: Theater & Room Management
- [ ] Define Room Layout (Rows & Columns, e.g., 10x12).
- [ ] Define specific seat types (VIP, Standard, Couple) causing price variations.

## Step 4: Sales Reporting & Dashboard
- [ ] **Stats:** Aggregate total tickets sold, revenue (daily/monthly).
- [ ] **Filter:** View revenue by Movie or by Theater.
- [ ] **Export:** Option to export data to Excel/CSV.