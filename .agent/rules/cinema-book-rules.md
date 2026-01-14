---
trigger: always_on
---

# Workspace Rules for Cinema-Book Project

## 1. Project Context & Architecture
- **Domain:** Cinema Booking System (Cinema-Book).
- **Structure:** Monorepo architecture.
  - **Frontend:** `frontend/` (ReactJS).
  - **Backend:** `backend/` (Laravel/PHP).
- **Documentation:** The root directory contains critical documentation. You must reference these files to understand the business logic and API contracts.

## 2. Key References (Must Read)
- **API Specifications:** Before writing any API integration code, ALWAYS check:
  - `@/INTEGRATED_APIS.md`: For the finalized list of integrated APIs.
  - `@/API_Bilingual.md`: For bilingual API descriptions and schema details.
- **Requirements:** Refer to `@/CineBook.pdf` or `@/luyenhao_CinemaBooking.pdf` for detailed project requirements and flow.

## 3. Workflow Integration
- Use the established workflows in `.agent/workflows/` for specific tasks. Do not reinvent the process if a workflow exists:
  - **Admin Tasks:** `@/.agent/workflows/admin-system-workflow.md`
  - **Booking Flow:** `@/.agent/workflows/booking-eticket-workflow.md`
  - **Auth/User:** `@/.agent/workflows/auth-user-workflow.md`

## 4. Coding Constraints
- **API Consistency:** Ensure all frontend API calls match the endpoints defined in `INTEGRATED_APIS.md`.
- **Frontend Paths:** All new UI components must be placed within `frontend/src/`.
- **Language:** Stick to the terminology used in `API_Bilingual.md` to ensure consistency between backend and frontend.
## 5. Language & Communication Protocols
- **Planning & Communication:** Tất cả giao tiếp, kế hoạch (planning) và giải thích phải bằng **Tiếng Việt**.
- **Code:** Tất cả mã nguồn (tên biến, comment, UI text) phải giữ nguyên **Tiếng Anh** (theo chuẩn Quốc tế).
