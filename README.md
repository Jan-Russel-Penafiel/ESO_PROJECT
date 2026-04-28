# ESO Fines Management System with Automated GCash Payment

A web-based fines tracking and collection portal for the **Educational Services Office (ESO)**.
Built with **non-OOP PHP**, **Tailwind CSS**, **JavaScript**, and **MySQL**.

---

## 1. Project Structure

```
fine/
├── index.php                    # Login page (entrypoint)
├── README.md                    # this file
│
├── includes/                    # reusable PHP (config, db, auth, helpers)
│   ├── config.php
│   ├── db.php
│   ├── auth.php
│   └── functions.php
│
├── templates/                   # UI layout fragments
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
│
├── actions/                     # form handlers / business logic
│   ├── login.php
│   ├── logout.php
│   ├── student_save.php
│   ├── student_delete.php
│   ├── category_save.php
│   ├── category_delete.php
│   ├── fine_save.php
│   ├── fine_cancel.php
│   ├── fine_mark_paid.php
│   ├── fine_delete.php
│   ├── payment_verify.php
│   ├── payment_fail.php
│   └── export_csv.php
│
├── admin/                       # admin pages
│   ├── dashboard.php
│   ├── students.php
│   ├── categories.php
│   ├── fines.php
│   ├── payments.php
│   └── reports.php
│
├── student/                     # student pages
│   ├── dashboard.php
│   ├── fines.php
│   ├── history.php
│   └── pay.php                  # QR + GCash deep-link
│
├── api/                         # JSON / callback endpoints
│   ├── payment_status.php       # polled by pay.php for live status
│   └── gcash_callback.php       # simulated GCash bridge / webhook
│
├── assets/                      # static assets (css/js/img)
│   ├── css/
│   ├── js/
│   └── img/
│
└── database/
    └── schema.sql               # full MySQL schema + seed data
```

---

## 2. Setup (XAMPP)

### 2.1 Place the project
Copy/clone this folder into `C:\xampp\htdocs\fine`.

### 2.2 Start XAMPP services
Start **Apache** and **MySQL** from the XAMPP control panel.

### 2.3 Create the database
1. Open phpMyAdmin → http://localhost/phpmyadmin
2. Click **Import** → choose `database/schema.sql` → **Go**.

This will:
* Create the `eso_fines` database
* Build all tables with foreign keys
* Seed an admin (`admin / Admin@123`) and two demo students (`juan / Student@123`, `maria / Student@123`)
* Seed five sample fine categories.

### 2.4 Configure (optional)
Edit `includes/config.php` if your DB user/password differs from XAMPP defaults
(`root` / no password). You can also change `GCASH_NUMBER` and `GCASH_MERCHANT_NAME`
to your real GCash account.

### 2.5 Run
Open: **http://localhost/fine/**

---

## 3. Default Logins

| Role     | Username | Password     |
|----------|----------|--------------|
| Admin    | admin    | Admin@123    |
| Student  | juan     | Student@123  |
| Student  | maria    | Student@123  |

---

## 4. Feature Walkthrough & Test Steps

### ✅ Feature 1 — Multi-role Login
1. Visit `http://localhost/fine/`.
2. Log in as `admin` → redirected to **Admin Dashboard**.
3. Log out, log in as `juan` → redirected to **Student Dashboard**.

**Expected:** Each role lands on a different dashboard. Wrong credentials show an inline error.

### ✅ Feature 2 — Admin: Manage Students (CRUD)
1. Go to **Students** in the sidebar.
2. Add: enter student no `2024-0099`, name, email, username `student3`, password `Pass@123`.
3. Edit: click the pencil → change course → **Update**.
4. Delete: click the trash icon → confirm.

**Expected:** New student appears in the table; can immediately log in with the new credentials.

### ✅ Feature 3 — Admin: Manage Fine Categories
1. Sidebar → **Fine Categories** → add a new one (e.g. "Vandalism" – ₱1000).
2. Edit / delete an existing one.

**Expected:** New category appears in the dropdown when issuing a fine.

### ✅ Feature 4 — Admin: Issue a Fine
1. Sidebar → **Fines** → pick student, choose category (auto-fills amount), submit.

**Expected:** Fine appears in the table with status `Unpaid` and reflects in admin/student dashboards.

### ✅ Feature 5 — Student: Real-time Fine Status
1. Log in as `juan`.
2. Dashboard auto-refreshes every 15s, KPIs update as admin adds/verifies fines.

### ✅ Feature 6 — QR Scanner / GCash Payment
1. As `juan`, click **Pay via GCash** beside an unpaid fine.
2. The `/student/pay.php` page shows:
   * Fine summary,
   * "Open GCash App" button (deep link `gcash://app`),
   * A QR code generated via api.qrserver.com pointing at our `/api/gcash_callback.php?ref=...`.
3. Scan the QR with any phone camera → it opens the **GCash bridge page**.
4. On the bridge page click **"I Have Sent the Payment"**.

**Expected:** The bridge page shows ✅ success. The pay.php page (still open on the laptop) auto-detects the change in ≤ 3s and redirects to **Payment History**.

### ✅ Feature 7 — Admin: Payment Monitoring
1. Sidebar → **Payments**.
2. See live counts for *Collected* / *In Flight* / *Transactions* and a filterable list.
3. For an `initiated` or `pending` row, click **Verify** to mark success, or **Fail** to mark failed.

**Expected:** Status updates instantly; corresponding fine flips to `paid` (or back to `unpaid` on failure).

### ✅ Feature 8 — Reports + CSV Export
1. Sidebar → **Reports**, pick a date range.
2. View KPIs by status, top categories, top offenders, and daily collection.
3. Click **Export CSV**.

**Expected:** A `.csv` file downloads with all fines in range.

### ✅ Feature 9 — Session Timeout
1. Log in, leave the page idle for 30 minutes (or change `SESSION_TIMEOUT_MINUTES` in `config.php` to `1` for quick testing).
2. Click any link.

**Expected:** You're returned to the login page with an "Your session expired" notice.

---

## 5. CLI / Browser Testing of the API

### Test JSON status endpoint
```bash
curl "http://localhost/fine/api/payment_status.php?ref=ESO-XXXXXXXX-YYYYYY"
```
Expected JSON:
```json
{"ok":true,"status":"initiated","amount":50,"paid_at":null}
```

### Test GCash callback (simulating a successful confirmation)
```bash
curl -X POST -d "action=confirm" \
  "http://localhost/fine/api/gcash_callback.php?ref=ESO-XXXXXXXX-YYYYYY"
```
Expected: HTML response with the "Payment Successful" panel; the matching payment row in DB flips to `success`, fine flips to `paid`.

---

## 6. GCash Integration Notes

* This build uses a **simulated bridge page** (`api/gcash_callback.php`) because GCash does not offer a public personal-account API.
* The QR code embeds the URL of that bridge page, so any QR scanner (including the GCash app's "Pay QR" if pointed at this URL) lands the user on the bridge.
* For a **real merchant integration** (PayMongo / Xendit / GCash Business):
  1. Replace `api/gcash_callback.php` with the provider's hosted checkout redirect URL.
  2. Add a webhook endpoint that flips `payments.status` based on the signed payload.
  3. Set `AUTO_CONFIRM` in `gcash_callback.php` to `false` so admin always verifies first.

---

## 7. Theming

Tailwind is pulled from CDN with a custom palette extending **emerald-green + white**.
You can change the brand color by editing the `tailwind.config` block in `templates/header.php` and `index.php`.

---

## 8. Security Notes

* All forms protected by **CSRF token** (`csrf_field()` / `csrf_check()`).
* All DB queries use **PDO prepared statements** — no raw concatenation.
* Passwords hashed with **bcrypt** (`password_hash` / `password_verify`).
* Sessions are HTTP-only and regenerated on login.
* Idle timeout enforced via `check_session_timeout()`.

---

## 9. Removing a Feature

Because of the modular layout, removing a feature means deleting its slice in three places:

| Layer       | Location                                    |
|-------------|---------------------------------------------|
| UI page     | `admin/<feature>.php` or `student/<feature>.php` |
| Form logic  | `actions/<feature>_*.php`                   |
| Sidebar link| `templates/sidebar.php`                     |
| DB schema   | corresponding tables in `database/schema.sql` |

---

Built for educational use. Replace seed credentials before deploying anywhere public.
