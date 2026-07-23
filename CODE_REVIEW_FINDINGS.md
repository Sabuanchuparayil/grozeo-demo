# Grozeo Platform — Application Logic Review

**Date:** 2026-07-23
**Scope:** All six services (application code only; third-party libraries, `vendor/`, `bin/`, `obj/`, and vendored JS/asset bundles excluded).
**Nature:** Defensive self-review to identify logic bugs, correctness problems, and unsafe patterns so the team can fix them.

---

## Executive Summary

The review covered all six repositories. Findings cluster into a few recurring themes that appear across multiple services:

1. **Money flows are not idempotent or atomic.** Wallet debits, stock decrements, refunds, settlements, and ledger postings frequently run without row locking, without database transactions, or without a "claim-before-act" guard. Retried jobs and concurrent requests can double-charge, double-refund, oversell stock, or post duplicate ledger entries.
2. **Ownership/scope checks are missing on record access.** Several API endpoints and admin actions load or mutate records by a client-supplied ID without confirming the record belongs to the current user, branch, or tenant.
3. **Payment verification is weak.** Gateway callbacks are trusted with insufficient signature/amount verification in places, and some post-payment side effects fail silently while the order is still marked successful.
4. **A systemic prepared-statement bug in the two PHP admin apps.** The custom "safe" query helpers are called with quoted `'?'` placeholders, so bound parameters never apply — this silently breaks updates/deletes and defeats the parameterization.
5. **Authentication inconsistencies.** Plaintext password acceptance, mismatched hash schemes, captcha bypasses, role-mapping errors, and self-asserted verification flags.
6. **Hardcoded secrets committed to source** across several services (SMS/SES credentials, Google API keys, sample gateway keys, seed superuser hashes).

### Severity totals (approximate, across all services)

| Service | Critical | High | Medium | Low |
|---------|:--------:|:----:|:------:|:---:|
| Grozeo-Bizapi (Laravel API) | 6 | 10 | 8 | 3 |
| Grozeo-Scheduler (Laravel worker) | 9 | 14 | 10 | 3 |
| Grozeo-Public (.NET 6 storefront) | 1 | 4 | 2 | — |
| Grozeo-Partner (.NET 8 portal) | 3 | 3 | 8 | 4 |
| Manage-Products (PHP admin) | 4 | 8 | 10 | 3 |
| Grozeo-Bizadmin (PHP back-office) | 7 | 8 | 8 | 3 |

> Note: Many "Critical/High" items are logic/correctness defects with security implications. Counts are indicative, not audited totals.

---

## Cross-Cutting Patterns (fix once, apply everywhere)

- **`'?'` placeholder bug (PHP apps):** `getItemSafe` / `executeSafe` / `getFromSafe` expect a bare `?`, but are called as `= '?'`. Binds never apply; queries compare against the literal character `?`. Also frequently paired with the wrong bind type (`"i"` for UUID/string keys). Grep `= '?'` under `Manage-Products/modules/` and `Grozeo-Bizadmin/modules/`.
- **`if (status)` instead of `if ($status)` (Manage-Products):** bare `status` is a truthy constant, so failed operations report success. Five+ modules affected.
- **Claim-before-act missing (Scheduler + Bizapi):** financial jobs perform the external side effect (gateway call, ledger post, settlement) *before* durably marking the record processed, so a retry re-runs it. Should set an intermediate "processing" state atomically and only finalize after success, with idempotency keys.
- **Unscoped record access (Bizapi + PHP admins):** load/update by ID without `WHERE customer_id = auth` / branch / tenant scoping.
- **Silent `catch (\Exception $e) {}` in money paths (Bizapi + Scheduler):** post-payment sales-order creation and finance postings are wrapped in empty catches while the order is marked success.
- **Committed secrets:** rotate all hardcoded credentials and move to environment variables.

---

## 1. Grozeo-Bizapi (Laravel 10 — customer API)

### Critical
1. **Wallet retry can complete an order as "wallet paid" with zero balance.** If wallet balance `<= 0` but a prior `WalletTransaction` exists for the order, the code reuses that amount and proceeds to `cashOnDelivery(..., isFullyWallet=true)` without funds. — `app/Modules/Checkout.php` ~236–262, 315–353
2. **Wallet debit has no row locking (double-spend race).** Read-modify-write on `cust_walletbalance` with no `lockForUpdate()` and no atomic `WHERE balance >= ?`. Concurrent `/checkout/confirm` can overspend. — `app/Modules/Checkout.php` ~315–326
3. **Razorpay: no webhook signature verification, no amount match.** Webhook path only checks the User-Agent string; success only checks `status === 'captured'` and never compares paid amount to the order. — `app/PaymentGateways/RazorPayment.php` ~41–114
4. **Confirm order / POD→online: no ownership check (IDOR).** Orders loaded by `order_group_id`/`order_id` only. — `app/Modules/Checkout.php` ~182–212, 356–363; `app/Http/Controllers/CheckoutController.php` ~118–131
5. **Coupon apply mutates any order by ID (IDOR).** No customer ownership check before updating totals. — `app/Http/Repositories/Coupon/Coupon.php` ~66–80, 230–234
6. **Stock decrement race; can go negative; wrong scope.** No lock; uses *all* blocked rows for the customer (not the order); deletes all customer blocked stock on one payment. — `app/Http/Repositories/Payment/AfterPayment.php` ~38–70

### High
7. **Paytm success flips ALL payment-initiated online orders for the customer** to the callback status. — `app/Http/Repositories/Payment/PaymentRepository.php` ~160–166
8. **Multiple order IDOR endpoints** (invoice, returnables/return, order details, track URL, order payload) filtered by storegroup only, not customer. — `OrderHistoryRepository.php` ~180–205, 322–325; `OrderInvoiceController.php` ~29–32; `OrderReturnController.php` ~33, 124–127; `OrderCompleteController.php` ~199–203
9. **Online+wallet sets `order_amount_payable` to 0** instead of `total - walletAmount` (mode 5). Breaks accounting/COD collection. — `app/Modules/Checkout.php` ~991–998
10. **Cancel refund ledger wrong for online+wallet (mode 5):** `WalletTransaction.brcw_Amount` set to full `$order->total`, double-counting vs balance. — `app/Http/Repositories/Order/OrderCancelledRepository.php` ~102–137
11. **Hardcoded fake order amount / random Paytm amount** (`order_total_amount => 5079`, `TXN_AMOUNT = rand(1000,10000)`). — `app/Http/Repositories/Checkout.php` ~95–104, 189
12. **`AuthController::refresh` echoes the client token** rather than performing a real JWT refresh; route lacks `jwt.verify`. — `app/Http/Controllers/AuthController.php` ~60–63; `routes/api.php` ~83–86
13. **JWT audience check disabled** (commented out) in two middlewares. — `app/Http/Middleware/JwtMiddleware.php` ~39–41; `AuthGuestMiddleware.php` ~41–43
14. **Post-payment side effects swallowed** (`salesOrders`, `finascopPosting` in empty catches) while order marked success. — `app/Http/Controllers/PaymentResultController.php` ~314–326
15. **Easebuzz `paymentComplete` returns undefined `$status`** → wrong branch in `paymentProcess`. — `app/PaymentGateways/EasebuzzPayment.php` ~91–104
16. **`BlockedProducts::markedForDelivery` deletes the entire customer cart** on paying for one order. — `app/Modules/BlockedProducts.php` ~31–37

### Medium
17. State machine allows rewriting statuses `<= SUCCESS(4)` (already-successful orders remain updatable). — `app/Modules/Checkout.php` ~994
18. Cancel allows any status `> 3` within the cancel window (includes delivered/out-for-delivery). — `OrderCancelledRepository.php` ~56–60
19. Public dummy JWT issued at `GET /initial` without auth. — `routes/api.php` ~249; `HomeScreenController.php` ~77–85
20. Order line items store cart prices while the header uses recalculated prices → tax/margin/refund mismatch. — `app/Modules/OrderCollect.php` ~231–233
21. Stock block created without inventory lock or remaining-qty re-check → oversell. — `app/Modules/OrderCollect.php` ~328–351
22. `getpaymentstatus`/`getOrder` have no ownership check. — `PaymentResultController.php` ~402–418
23. Stripe secret in a source comment. — `app/PaymentGateways/StripePayment.php` ~47
24. `me`/`logout`/`refresh` lack auth middleware in routes. — `routes/api.php` ~83–86

### Low
25. SQL built via string concatenation (safe today via auth/header, but fragile). — `Checkout.php` ~657; `PaymentResultController.php` ~494, 542
26. Return qty update not scoped to order. — `OrderReturnController.php` ~140–147
27. `PaymentRepository::updateOrderDetails` trusts client `order_id` with no customer filter. — `PaymentRepository.php` ~90–100

**Top fixes:** atomic wallet debit + remove "reuse old WalletTransaction as free payment"; Razorpay signature + amount verification; enforce `order_customer_id = auth` on confirm/coupon/invoice/return/details/status; scope stock to `order_id` with `lockForUpdate`; fix Paytm bulk update and mode-5 payable/refund ledger.

---

## 2. Grozeo-Scheduler (Laravel 10 — background worker)

### Critical
1. **Duplicate transfer orders for successful B2C orders.** Regular branches with no delivery slot never leave `SUCCESS`, so each tick re-selects and re-runs `transferOrders`; `B2CToTransferOrder` never checks for an existing `fstr_id`. — `app/Schedulers/OrderStatusUpdate.php` 37–94, 156–180; `app/Http/Services/B2CToTransferOrder.php` 25–95
2. **Stripe refunds marked successful even when the API throws.** Exception is caught but still returned as a truthy array; caller treats any truthy return as success. — `app/PaymentGateways/StripePayment.php` 435–464; `app/Schedulers/CustomerOrderRefunds.php` 30–44
3. **Razorpay refund amount not converted to paise** (Stripe is). Same major-unit amount sent raw → under/over-refund. — `app/PaymentGateways/RazorPayment.php` 517–531
4. **Refund status set to `'2'` before the gateway call; failed refunds stuck forever, no idempotency key.** Invalid-amount path also reuses status `2` for `REFUND_CANCELLED`. — `app/Schedulers/CustomerOrderRefunds.php` 20–55
5. **Merchant settlements can overlap and double-settle.** No `withoutOverlapping()`; orders not claimed in PHP before the external API/`insertSettlementData` call. — `app/Console/Kernel.php` 78; `app/Schedulers/MerchantSettlements.php` 21–52
6. **Finance payout aggregation groups only by `bank_id`** while selecting branch/account fields → merchants sharing a bank merged into one payout; concurrent runs duplicate. — `app/Schedulers/FinanceTransaction.php` 17–55; `Kernel.php` 81
7. **Auto-posting runs finance side effects before marking the event processed** (DynamoDB `neStatus = 1` set last) → crash/exception re-posts the ledger. — `app/Schedulers/PostingScheduler/Postings/AutoPostingNew.php` 49–71
8. **Hardcoded SMS API credentials in source.** — `app/Sms/TextLocalSms.php` 148
9. **Hardcoded Google API keys as defaults/fallbacks.** — `config/app.php` 71, 230; `app/Helpers/helpers.php` 95

### High
10. Razorpay success path references undefined `$paymentData` → exception; outer catch leaves refund at `2` while money moved. — `RazorPayment.php` 458–468
11. Unsafe string SQL in settlements (`CALL insertSettlementData('...')` with interpolated bank/account fields). — `MerchantSettlements.php` 50
12. `AssignOrder` `orWhere` precedence bypasses `ordertype`/`openingtime` filters. — `AssignOrder.php` 42–48
13. Partner delivery crons lack `withoutOverlapping`. — `Kernel.php` 84–95
14. Delayed API cancel marks Dynamo status done even if cancel fails. — `ScheduledDelays/DelayedActions/DelayedAPICancellations.php` 45–61
15. Delayed merchant cancel runs before Dynamo claim → double cancel on retry. — `DelayedMerchantCancellations.php` 45–63
16. `CreatePacking` can re-hit Petpooja if the local row insert fails/races. — `CreatePacking.php` 24–45; `PackingPartners/Petpooja/Petpooja.php` 24–51
17. Shipping/express consignment sets claim flag `2` before booking; failure leaves it stuck forever. — `CreateShippingConsignment.php` 38–58; `CreateExpressConsignment.php` 71–101
18. `CheckOrderFailed` swallows sales-order errors; `markOrderFailed` SMS has no "already notified" guard (spam risk). — `CheckOrderFailed.php` 71–80, 121–133
19. Payment timeout vs live-payment race (no locking against concurrent webhook success). — `CheckOrderTimeout.php` 35–52
20. `ScheduleNewBookings::updateSchedule` uses a Dynamo hash as numeric `quor_id` + unquoted datetimes in `BETWEEN` → can mass-update unrelated rows. — `Drivers/ScheduleNewBookings.php` 218–222
21. Lat/lng swapped for secondary driver candidates → wrong geo box. — `Drivers/ScheduleNewBookings.php` 298
22. Computed hold status never applied; CPR branch uses an uninitialized variable. — `OrderStatusUpdate.php` 47–59, 131–146
23. Dark-store delivery start time overwritten when `$data` is replaced with only `status_id`. — `OrderStatusUpdate.php` 120–129

### Medium
24. Financial catch blocks only log and unlock (no alert, no failed-job retry) — `CustomerOrderRefunds`, `MerchantSettlements`, `FinanceTransaction`, `AutoPostingNew`.
25. Partner delivery checks pass a possibly-null consignment into partner APIs. — `PartnerDeliveryStartedCheck.php` 27–43; `PartnerDeliveryCompletedCheck.php` 27–37
26. `getHolidays` returns bool but callers use `in_array` → holiday skipping broken. — `Drivers/ScheduleNewBookings.php` 186–196, 242–246
27. Packing delay IVR/outbound can re-call on partial failure. — `Supports/PackingDelayCalls.php` 38–72; `PackingDelayManualCalls.php` 19–46
28. Stripe SSL verification disabled. — `StripePayment.php` 49
29. Queue default `sync` + short `retry_after` for async partner work. — `config/queue.php` 16, 82–86
30. Commented live-looking Stripe secret. — `StripePayment.php` 46
31. Sample API key in request-class comments. — `Modules/BackOffice/Http/Requests/AgentStoresGroupUpdateRequest.php` 42; `AgentStoresGroupAddRequest.php` 42
32. Stripe webhook fee parsing uses an undefined `$key` index → wrong/zero tax. — `StripePayment.php` 133–140
33. Revolut `$$response` variable-variable typo → empty checkout URL. — `RevolutPayment.php` 84

### Low
34. Timezone fixed to Asia/Kolkata while settlements use `date()`; cutoffs skew for non-IST ops. — `config/app.php` 100; `MerchantSettlements.php` 23
35. `ProcessLock` polarity inconsistent; not used as a real mutex. — various
36. `SendNotificationsRepository` SMS/email stubs are empty no-ops. — `app/Http/Repositories/SendNotificationsRepository.php` 89–94

**Top fixes:** duplicate transfer orders (#1); Stripe false-success refunds (#2); Razorpay amount units (#3); settlement/finance overlap + grouping (#5–6); autoposting claim-before-post (#7); rotate secrets (#8–9).

---

## 3. Grozeo-Public (.NET 6 — customer storefront)

> Mostly a thin proxy to the backend API, and money math is recomputed server-side from an `order_group_id` (client-sent amounts are not trusted). The real issues are in tenant resolution and identity handling.

### Critical
1. **Tenant chosen from a client-controlled `X-Forwarded-Host` header** with forwarded-headers allow-lists cleared (`KnownNetworks`/`KnownProxies` empty). The resolved tenant sets the backend API base URL, `defaultstoregroupid`, payment gateway, and branding → cross-tenant isolation bypass. Unknown hosts also silently fall back to the *first* tenant. — `Retaline/Service/Tenant/AppTenantResolver.cs` 51–71, 96, 104; `Retaline/Startup.cs` 181–191

### High
2. **Self-service verification bypass (age gate).** Logged-in users can flip their own `AgeVerified`/`EmailVerified`/`PhoneVerified` with no server-side check; `AgeVerified` gates age-restricted products. — `Retaline/Controllers/AuthenticationController.cs` 280–299, 319–335
3. **Hardcoded impersonation OTP `"1111"`.** Impersonation logs in as an arbitrary customer via a static OTP (role-gated, but the backend accepts a fixed OTP). — `Retaline/Core/Services/Authentication/CustomAuthenticationService.cs` 147–157
4. **Auth cookie `SameSite=None`, no explicit `Secure`, antiforgery disabled/commented.** Form-posting endpoints (`CheckoutController.SubmitOrder`/`Checkout`) are CSRF-reachable. — `Retaline/Startup.cs` 140–151; `AuthenticationController.cs` 109
5. **Fake "AES" encryption is just Base64** (`Encrypt`/`Decrypt`, `Common.EncryptString`) — no confidentiality/integrity. — `Retaline/Core/Services/HelperServices/HttpHelperService.cs` 440–452

### Medium
6. **Guest-session inverted condition** regenerates a fresh guest every time (`if (guest != null) guest = CreateNewGuestUser();` should be `== null`). — `HttpHelperService.cs` 374–377
7. **Client `PaymentMethod`/`NetAmount` used for flow control** (`if (paymentMethod == 1 || checkout.NetAmount == 0)`). Charged amount is recomputed server-side, but branch selection is client-driven; `SubmitOrder` returns `null` (empty 200) when checkout is disabled. — `CheckoutController.cs` 63, 83

**Top fixes:** resolve tenant from a trusted host only (configure real `KnownProxies`/`KnownNetworks`, drop first-tenant fallback); remove client self-assertion of verification flags; replace static impersonation OTP with a server-issued grant; set cookie `Secure` + `SameSite=Lax/Strict` and add antiforgery; replace Base64 "encryption"; fix the guest condition.

---

## 4. Grozeo-Partner (.NET 8 — merchant/partner portal)

> Most Controllers/Services are still stubs (empty finance/order/settlement logic), so there is little business math to review yet. Defects concentrate in auth, tenancy claims, and cookie/session config. Several stubbed actions `return View()` for views that don't exist (runtime 500s once reached).

### Critical
1. **`RoleId` aliased as `BranchId`.** SQL does `RoleId AS BranchId` and stores it as the branch claim; `RoleId` is a role FK, not a branch. Any future query scoping on `BranchId` targets the wrong ID. `GetAllAsync` similarly filters users by role, not branch. — `Services/UserService.cs` 16–17, 34–35, 42–45
2. **Plaintext password accepted.** After the MD5 fallback, login succeeds if the submitted password equals the stored `Passwd` literal. — `Services/UserService.cs` 56–58
3. **Every non-super user is forced to `TenantAdmin`,** and Finance/Support/Marketing policies include `TenantAdmin` → every active merchant user gets full admin-area access. — `Services/UserService.cs` 17; `Program.cs` 24–27; `Areas/Account/Controllers/AccountController.cs` 53

### High
4. Production cookies require HTTPS (`CookieSecurePolicy.Always`) while Kestrel binds HTTP only with no forwarded-headers setup → sessions may silently fail behind a bare-HTTP/misconfigured proxy. — `Program.cs` 15–17, 36–38, 41–42
5. Auth policies reference roles (`Finance`, `Support`) that login never assigns → intended least-privilege users can't exist; unused policies never apply. — `Program.cs` 24–27
6. `GetByIdAsync` ignores active/deleted flags and returns the password hash into the app layer. — `Services/UserService.cs` 32–37

### Medium
7. Error page throws NullReference (`Error()` returns `View()` with no model; view reads `Model.ShowRequestId`). — `Controllers/HomeController.cs` 10; `Views/Shared/Error.cshtml` 8
8. `AccessDenied` action has no view → authorization failures become a secondary 500. — `Areas/Account/Controllers/AccountController.cs` 82
9. "Remember me" checkbox has no `value="true"` → posts `on`, never binds to `true`, persistence never applies. — `Areas/Account/Views/Account/Login.cshtml` 42
10. Login input `type="email"` blocks the username login the query allows. — `Login.cshtml` 35
11. HSTS header appended on every response including Development/HTTP. — `Program.cs` 65–72
12. `DataService` exposes no transaction API → future multi-step writes can't be atomic. — `Services/DataService.cs` 20–36
13. Many controller actions `return View()` for non-existent views (Finance/Tenant stubs). — e.g. `Areas/Finance/Controllers/ReportController.cs` 20–24
14. Auth cookie lifetime (8h/7d) vs session idle (60m) mismatch → authenticated-but-empty-session bugs. — `Program.cs` 12–13, 33

### Low
15. Dev DB credentials committed. — `appsettings.Development.json` 10
16. Unsalted MD5 legacy verification (should migrate-on-login). — `UserService.cs` 56–57
17. `BCrypt.Verify` can throw on malformed `$2…` hashes → 500 instead of failing closed. — `UserService.cs` 53–54
18. `AllowedHosts: "*"`. — `appsettings.json` 9

**Top fixes:** stop mapping `RoleId`→`BranchId` (load real branch/company); remove plaintext password compare; map real roles instead of binary SuperAdmin/TenantAdmin; align cookie Secure policy with actual HTTPS termination.

---

## 5. Manage-Products (legacy PHP — product/inventory admin)

### Critical
1. **Quoted `'?'` placeholders — prepared binds never apply.** Return-to-stock, PO assign, contract PO, composition deletes, and PO prerequest all silently miss rows (and use wrong bind type `"i"` for string keys). — `modules/order_processing/index.php` 345, 375; `modules/finascop_purchase_order/index.php` 1113, 1117; `modules/finascop_contract_po/index.php` 52, 138, 190; `modules/mypha_composition/index.php` 187, 205, 217; `modules/finascop_po_prerequest/index.php` 164
2. **App login bypasses password; `ak` is predictable.** Valid `mf`+`ak` sets `$passwordMatch = true` and loads any `IsAppUser = 1` user; `validateAuthKey` is `md5(date("dMyH").'KTC')` with a broken regex; `validateMachineFinger` concatenates input into SQL. — `modules/auth/authenticate.php` 21–55, 68–69; `modules/auth/functions.php` 50–74
3. **Hardcoded credentials in source** (live AWS SES SMTP user/password; ScraperAPI key with no auth wrapper). — `schedule/Temp/smsemail.php` 52–54, 89; `schedule/smsemail.php` 52–54; `modules/mypha_product/scraperapi.php` 3–5 — **rotate immediately**
4. **COD+wallet return under-credits the cash portion** — reduces `$item_sales_price` by `$codAmt` but never adds `$codAmt` to `order_amount_returnon_cash`. — `modules/order_processing/index.php` 431–437

### High
5. `if (status)` without `$` → delete success always reported (5 modules). — `finascop_purchase_order/index.php` 1132; `finascop_po_prerequest/index.php` 153; `finascop_contract_po/index.php` 219; `retaline_sales_request/index.php` 770; `retaline_pincodegroup/index.php` 209
6. Unparameterized DELETE/UPDATE from request input (multiple modules; representative: `finascop_purchase_order/index.php` 1125–1128; `user/delete.php` 14–16; `ui/index.php` 28).
7. `extract($_GET/$_POST)` + unsanitized module include (`include("modules/$module/index.php")`, no allowlist). — `index.php` 58–67; `init_modules.php` 3, 35
8. Auth-gate holes: API-key validation for mutating ops commented out; `email_action`/`vblogin` run with no session. — `index.php` 102–121, 131–135
9. Sales return overwrites returned qty instead of adding → corrupts return history. — `modules/finascop_sale/index.php` 721
10. Stock conversion: no qty validation, can go negative, missing-branch → `WHERE stbr_id = 0`. — `modules/finascop_stock/index.php` 376–415
11. Permission string mangled by `$pems` typo (`strlen($pems)` undefined) → wrong grants. — `modules/access/save.php` 23
12. Online payment timeout scheduler iterates an undefined variable (`getMultipleData` commented out). — `schedule/OnlinePaymentValidator.php` 17–19

### Medium
13. `session_regenerate_id()` on every request (no `true`) → concurrent AJAX session loss; cookie has no HttpOnly/Secure/SameSite. — `index.php` 13–21
14. Remember-me query uses `IsActive=%d` with `1` while auth uses `'Yes'`; password from cookie concatenated into SQL. — `modules/auth/remember.php` 29
15. `blocked_capabilities` builds `IN ()` when empty; `capability <> NULL` always unknown. — `includes/lib.php` 107–114
16. Offer list: filter built from raw POST but not applied to the list query; `$sort` overwritten twice. — `modules/offer_management/index.php` 16–35
17. B2B item search produces invalid SQL when query is empty. — `modules/retaline_sales/index.php` 849–850
18. Contract PO list missing branch scope (filter commented out). — `modules/finascop_contract_po/index.php` 173
19. Division-by-zero risk on return price. — `modules/order_processing/index.php` 401
20. SMS queue success path doesn't run the update; success email log uses undefined `$error`. — `schedule/smsemail.php` 14–21, 103–107
21. `user_access` embeds raw `$module`/`$operation` into SQL. — `includes/lib.php` 45–48
22. Standalone scraper script directly callable, hardcoded key, no session check. — `modules/mypha_product/scraperapi.php`

### Low
23. Local DB credentials in committed config (dev-only). — `includes/config.php` 7–8
24. Dead/copy-paste in role save. — `modules/role/save.php` 49
25. Auth blank-check operator precedence allows empty username with captcha filled. — `modules/auth/authenticate.php` 33

**Top fixes:** quoted placeholders in return-to-stock + PO assign; auth `mf`/`ak` bypass; rotate credentials; `if (status)` → `if ($status)`.

---

## 6. Grozeo-Bizadmin (legacy PHP — business back-office)

### Critical
1. **Quoted `'?'` placeholders — binds never apply (systemic).** Optimistic-lock counts stay 0 (duplicate inserts), branch lookups fail, stock/barcode updates miss rows. — `modules/retaline_grn/index.php` 15, 24–25, 73; `modules/finascop_purchase_order/index.php` 84, 110, 280, 1125, 1140; `modules/order_processing/index.php` 367, 397; `modules/finascop_stock_upload/index.php` 817, 1032, 1161; plus `finascop_po_prerequest`, `finascop_contract_po`, `retaline_sales_request`, `order_cancellation`, `retaline_return_request`, etc.
2. **Contract PO update WHERE uses a typo'd POST key with a trailing space** (`$_POST['fpot_uniqueid ']`) → updates `fcpo_uniqueid = ''`. — `modules/finascop_contract_po/index.php` 60
3. **`updatedate()` uses undefined `$acet_NO` and clobbers `$data`** → WHERE on empty id (broad/wrong updates), transaction date becomes null, wrong `acet_ApprovedBy`. — `includes/finascop_accounts_Transactions.php` 687–706
4. **Ledger balance ignores `actr_IsNegative`** — approval math signs by `actr_IsNegative`, but `updateLedgerBalance` signs by debtor flag only → wrong debit/credit posting. — `includes/finascop_accounts_Transactions.php` 79–98 vs 618–654
5. **Login password check incompatible with `password_hash` storage** — plain `==` compare at login vs bcrypt at set; "current password" checks MD5; `setUserPassword` never verifies current password. — `modules/auth/authenticate.php` 53; `modules/user/index.php` 17–35, 63–70
6. **Captcha bypass for a hardcoded account + remember-me cookie carries the password** (`UserId:Passwd:microtime`). — `modules/auth/authenticate.php` 36, 167–171; `modules/auth/remember.php` 29
7. **Seed superuser credentials committed** (`retaline`, MD5 of `123456`) plus API keys. — `db/retalineEnterprise.sql` 104–109 — dangerous if loaded into a shared/prod DB

### High
8. Widespread SQL concatenation of request input (repo-wide in `modules/*/index.php`; representative: `retaline_reports/index.php` 52, 175; `order_cancellation/index.php` 143–145, 196; `finascop_approval/index.php` 228–238; `finascop_ledger/index.php` 16, 29; `includes/functions.php` 610–611; `includes/lib.php` 45–47).
9. `extract($_GET/$_POST)` + unsanitized module include. — `index.php` 62–71; `init_modules.php` 35
10. API-key validation commented out (session alone gates mutations). — `index.php` 106–125
11. Bank recon approve commits after per-row failures (partial approval + false "Data Saved"). — `modules/finascop_accounts/index.php` 243–286
12. Approval `updateStatus` failure leaves an open transaction (no rollback). — `modules/finascop_approval/index.php` 191–204
13. Order-cancel margin math + wrong DB helper (`getItemFromDB` returns first column; foreach over scalar; margin % applied to full order total per barcode → overstated refunds). — `modules/order_cancellation/index.php` 213–241
14. Inventory restock on cancel not batch-scoped (`fsbg_id` missing) → wrong batch incremented. — `modules/order_cancellation/index.php` 257
15. GRN stores MRP = offer rate → corrupts downstream margin/pricing. — `modules/retaline_grn/index.php` 55

### Medium
16. Session cookie missing HttpOnly/Secure/SameSite; `session_regenerate_id()` every request; cookie domain/path wrong behind proxies. — `index.php` 13–21
17. Empty catch swallows DB failures in `getMulipleData`. — `includes/lib.php` 531–533
18. Contract PO packing qty can be undefined → MRP/landing/MMG become 0/null. — `modules/finascop_contract_po/index.php` 98–122
19. Offer search overwrites the looked-up name with raw POST (uses ID as name). — `modules/offer_management/index.php` 63–66
20. `user_access` treats empty capability (`'0'`) as allow-any-authenticated. — `includes/lib.php` 63–64
21. Approval UI marks both Approved and RollBack for status 3. — `modules/finascop_approval/index.php` 161
22. Local DB credentials in config. — `includes/config.php` 6–7
23. Scheduler lock exit without rollback → held locks. — `schedule/finascopScheduler.php` 30–38

### Low
24. Unauthenticated `email_action`/`vblogin` routes reference missing module dirs (fatal if called). — `index.php` 137–139
25. Hardcoded AWS placeholder keys. — `includes/config.php` 40–44
26. `Encrass` uses an all-zero IV (deterministic encryption of plaintext passwords). — `modules/user/index.php` 28

**Top fixes:** fix all `'?'` → `?` with correct bind types; fix `updatedate()` and ledger `actr_IsNegative` sign; unify the password scheme with `password_verify` and remove captcha bypass / password-in-cookie; fix contract PO key typo, GRN MRP field, and order-cancel scoping; parameterize POST-driven SQL and stop `extract()`; add rollback on failure in approval/recon paths.

---

## Recommended Remediation Sequence

**Phase 1 — Money integrity (highest risk):**
- Bizapi: atomic wallet debit; order-scoped stock decrement with locking; Razorpay signature + amount verification; fix mode-5 payable/refund ledger; fix Paytm bulk status update.
- Scheduler: make refunds/settlements/postings idempotent (claim-before-act + idempotency keys); fix Stripe false-success and Razorpay paise conversion; add `withoutOverlapping()` to financial crons; fix `bank_id`-only grouping.
- Bizadmin: fix ledger `actr_IsNegative` sign and `updatedate()`; fix order-cancel margin/inventory scoping.

**Phase 2 — Access control:**
- Bizapi: enforce `order_customer_id = auth` on confirm/coupon/invoice/return/details/status; re-enable JWT audience check; fix `refresh`.
- Public: trusted tenant resolution; remove self-asserted verification flags; server-issued impersonation grant.
- Partner: fix `RoleId`→`BranchId`, real role mapping, remove plaintext password path.
- PHP apps: fix the app-login bypass and captcha exemption; unify password scheme.

**Phase 3 — Correctness cleanups:**
- PHP apps: global fix for `'?'` placeholders and `if (status)`; parameterize SQL; remove `extract()` + allowlist module includes.
- All: replace empty `catch {}` in critical paths with real handling/alerting; stop marking success before verifying side effects.

**Phase 4 — Secrets & config:**
- Rotate and remove all hardcoded credentials (SES/SMS, Google, gateway keys, seed superusers); move to environment variables; fix cookie Secure/SameSite and HTTPS termination.

---

*This document is a review only; no application code was modified.*
