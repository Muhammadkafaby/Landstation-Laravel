# Test Coverage Standards

- Prefer feature tests for HTTP flows and business rules.
- Add focused unit tests for pricing and availability logic.
- Cover these critical flows first:
  - booking availability and overlap prevention
  - start/pause/resume/stop session billing
  - cash payment and QRIS manual verification
  - permission checks for admin/cashier actions
- Treat warnings separately from test/build failures, but record them.
