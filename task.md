# Database Audit and Flow Standardization

## Planning Phase
- [x] Audit all tables in `cinema_booking`
- [x] Identify redundant/useless tables
- [x] Map out the "Standard Flow" (User -> Booking -> Seat Lock -> Transaction)
- [x] Identify missing foreign key constraints or logic breaks
- [x] Create implementation plan for cleanup and flow strengthening

## Implementation Phase - Cleanup
- [x] Delete identified redundant tables
- [x] Remove unused columns
- [x] Clean up orphan records (if any)

## Implementation Phase - Flow Standardization
- [x] Add missing foreign keys and indexes
- [x] Implement cascaded deletes where appropriate
- [x] Strengthen seat locking/release logic if needed
- [x] Validate transaction -> booking integrity

## Verification Phase
- [x] Verify 3NF compliance
- [x] Test core flows (Booking, Payment, Seat selection)
- [x] Audit APIs and create API.md
- [x] Final project status report
