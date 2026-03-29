# Land Station MVP Master Plan

## Goal
Build the Land Station v1 web system with customer website, admin dashboard, POS, booking, per-minute billing, and QRIS manual payment.

## Architecture
- Laravel 12 monolith with Inertia React frontend.
- Domain modules split into `Public`, `Admin`, and `Pos` interfaces.
- Core services handle availability, pricing, billing, promotion, and payment verification.

## Components
1. Foundation scaffold and app shells
2. Access control and staff auth
3. Master data and settings
4. Booking and availability
5. POS timer and billing
6. Cafe ordering and checkout
7. Dashboard and reports
8. Hardening and audit

## Current Execution Slice
- Component 2: Access control and staff auth refinement
