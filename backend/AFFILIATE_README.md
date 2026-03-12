# Affiliate (Promoter) System

## Summary

- **Promoter signup:** `/promoter/register` (frontend) → `api/promoter/register.php`. Admin approval required.
- **Promoter login:** `/promoter/login` → `api/promoter/login.php`. Only approved promoters can log in.
- **Referral links:** `https://yoursite.com/product/123?ref=PROMOCODE`. Product page calls `api/referral/track.php?ref=...&product_id=...` (sets 30-day cookie, logs click).
- **Checkout:** Checkout sends `promoter_id` and `referral_code` from cookie (or body). `api/orders.php` POST creates order and commission (pending). Self-referral is blocked.
- **Commission:** 10%. Approved when order status → **delivered**; rejected when → **cancelled** (admin order status update triggers this).
- **Admin:** **Promoters** – list, approve/reject/suspend. **Commissions** – list by status. **Orders** – change status (delivered/cancelled) to approve/reject commission.

## DB

Run once (if not already):

```bash
mysql -u root meesho_ecommerce < backend/database/affiliate_schema.sql
```

Tables: `promoter_profiles`, `referral_clicks`, `commissions`, `withdrawal_requests`. Columns `promoter_id`, `referral_code` on `orders`.

## Frontend API base

Set `REACT_APP_API_URL` to your backend API base (e.g. `http://localhost/Shopping/backend/api`) so referral track and checkout hit the same origin/cookie domain.
