# Prime Foundation Academy — School Management System

A production-oriented school management platform for a Nigerian school, built with
Laravel 13, Blade, Tailwind CSS and Alpine.js. It manages students, parents/guardians,
teachers, classes, subjects, attendance, assessments/results, report cards, fees,
payments, announcements, users/roles/permissions, audit logs and reporting.

---

## 1. What was built

A full-stack Laravel application (not a prototype) covering:

- **Authentication & authorization** — Laravel Breeze (Blade) auth, five roles
  (Super Admin, Administrator, Teacher, Student, Parent/Guardian) via
  `spatie/laravel-permission`, and Policy classes enforcing per-record access
  (a teacher only sees their own classes; a parent only sees their own children).
- **School settings** — logo, motto, address, contact info, principal name,
  registration number, signatures, currency symbol, examination max score,
  ranking method and report-card footer, editable from the UI and used
  throughout the app (no hard-coded school info).
- **Academic structure** — academic sessions and terms (activate / close /
  reopen), classes and class arms (not hard-coded — admins create
  Nursery/Primary/JSS/SS classes and arms like "JSS 1A"), subjects, and
  teacher-to-class-subject assignments.
- **Teachers** — profiles, photos, qualifications, employment status, and
  class/subject assignments.
- **Students & guardians** — full admission workflow with auto-generated
  admission numbers, multiple guardians per student, a tabbed profile
  (Overview / Academic / Attendance / Results / Fees / Documents), and
  **promotion with full, reversible history** (`enrollments` table — old
  class records are never overwritten or deleted).
- **Attendance** — per class/arm/date capture (Present/Absent/Late/Excused),
  duplicate-proof, mobile-friendly, with history and summary reports.
- **Assessments & results** — configurable assessment components (CA1, CA2,
  Assignment, Test, …) and a configurable grading scale; a `ResultService`
  computes totals/grades and **class positions** (standard-competition or
  dense ranking, ties handled), through a
  **Draft → Submitted → Reviewed → Approved → Published** workflow.
  Published results are protected from silent recalculation.
- **Report cards** — professional A4 report cards, viewable on screen
  (print-ready) and downloadable as PDF (dompdf), pulling school branding
  and signatures from settings.
- **Fees & payments** — configurable fee categories/structures, bulk
  assignment to a class, partial payments, auto-generated receipt numbers,
  printable/PDF receipts, outstanding-fee tracking, all in Nigerian Naira (₦).
- **Announcements** — targeted to everyone / teachers / students / parents /
  a specific class.
- **Documents** — secure, non-public file storage (local disk) with
  authorization-checked download — never a guessable public URL.
- **Reports** — student, attendance, academic/results, financial and teacher
  reports, filterable by session/term/class/date, print-ready.
- **Audit logs** — automatic change history (user, action, entity, old/new
  data, IP, timestamp) on students, teachers, results, payments, fees,
  settings and users via an `Auditable` trait.
- **Student/Parent self-service portal** — "My Results", "My Attendance",
  "My Fees", with parents able to switch between multiple children.
- **Responsive UI** — sidebar + top nav, dashboard cards/charts, tables,
  search & filters, pagination, modals, toasts, empty states, confirmation
  dialogs — usable on desktop, tablet and phone (attendance/score entry are
  built mobile-first).

### Not built (explicitly out of scope for this pass)

Online admission/payments, SMS/WhatsApp notifications, timetable, library,
inventory, payroll, transport, hostel, online exams, e-learning, a REST API,
and multi-school/multi-campus support. The architecture (service classes,
policies, form requests, normalized schema) is intentionally structured so
these can be added later without reworking the core.

---

## 2. Main features by role

| Role | Can do |
|---|---|
| **Super Admin** | Everything below, plus manage roles/permissions and see the audit log. |
| **Administrator** | Manage students, teachers, guardians, academic structure, attendance, assessments/results workflow, fees/payments, announcements, reports, users. |
| **Teacher** | View assigned classes/subjects/students, take attendance, enter scores, submit results for review, post announcements. |
| **Student** | View own profile, results (once published), attendance, fee status, announcements. |
| **Parent/Guardian** | View linked children's profiles, results, attendance, fee status and payment history; switch between multiple children. |

---

## 3. Database structure

35 tables, fully normalized, with foreign keys, unique constraints, indexes
and soft deletes where it matters (students, teachers, guardians, subjects).

**Identity & access**: `users`, `roles`, `permissions`, `model_has_roles`,
`model_has_permissions`, `role_has_permissions` (spatie/laravel-permission).

**School configuration**: `school_settings`, `grading_scales`,
`assessment_types`.

**Academic structure**: `academic_sessions`, `terms`, `classes` (model
`SchoolClass` — `Class` is a reserved PHP word), `class_arms`, `subjects`,
`class_subject`, `teacher_assignments`.

**People**: `teachers`, `students`, `guardians`, `guardian_student`,
`enrollments` (promotion/class history — append-only), `student_subjects`.

**Academics**: `attendance`, `assessment_scores`, `examination_scores`,
`results` (with the full workflow status + reviewer/approver/publisher
audit fields).

**Finance**: `fee_categories`, `fee_structures`, `student_fees`, `payments`.

**Communication & files**: `announcements`, `documents`.

**Governance**: `audit_logs`.

Key relationships: a `Student` belongs to a `ClassArm` (current) and has
many `Enrollment`s (history); a `ClassArm` belongs to a `SchoolClass` and
has a `Teacher` as class teacher; `Result` rows are unique per
(student, subject, term); `Payment`s belong to a `StudentFee`, which tracks
`amount_due`/`amount_paid`/`balance`.

---

## 4. Development login credentials

Seeded by `php artisan db:seed`. **These are development-only credentials —
change them (or delete these accounts and create real ones) before any
real deployment.**

| Role | Email | Password |
|---|---|---|
| Super Admin | `superadmin@primefoundationacademy.example` | `SuperAdmin@2026` |
| Administrator | `admin@primefoundationacademy.example` | `Administrator@2026` |
| Teacher (class teacher, JSS 1A) | `teacher@primefoundationacademy.example` | `Teacher@2026` |
| Student (JSS 1A) | `student@primefoundationacademy.example` | `Student@2026` |
| Parent/Guardian | `parent@primefoundationacademy.example` | `Parent@2026` |

The seeder also creates two more teachers, eight more students in JSS 1A
with their own guardians, a sample fee structure with a partial payment,
ten days of attendance, scored/graded/ranked results for JSS 1A's three
core subjects (the demo student's results are published so the report
card and dashboards have real data to show), and four published
announcements.

---

## 5. How to run the application locally

**Requirements**: PHP 8.2+, Composer 2, MySQL 8 / MariaDB 10.6+, Node 18+.

```bash
git clone <this-repo>
cd alx-system_engineering-devops

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prime_foundation_sms
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Create the database, then continue with sections 6–9 below. Finally:

```bash
php artisan storage:link   # required for uploaded logos/photos/signatures
php artisan serve          # http://127.0.0.1:8000
npm run dev                # in a second terminal, for hot-reloading assets
```

## 6. How to migrate the database

```bash
php artisan migrate
# or, to rebuild from scratch during development:
php artisan migrate:fresh
```

## 7. How to seed the database

```bash
php artisan db:seed
# or in one step with a fresh schema:
php artisan migrate:fresh --seed
```

## 8. How to run tests

The test suite defaults to an in-memory SQLite database (see `phpunit.xml`),
so it needs no external database server.

```bash
php artisan test
# or
./vendor/bin/phpunit
```

53 feature tests cover authentication, authorization boundaries per role,
student registration/promotion (with history preservation and reversal),
attendance (including duplicate prevention), grade and class-position
calculation (both ranking methods, with ties), fee/payment handling, and
the full result draft→submitted→reviewed→approved→published workflow
including visibility rules for students/parents.

## 9. How to build frontend assets

```bash
npm run dev     # development, with hot module reloading
npm run build   # production build (outputs to public/build)
```

---

## 10. Production deployment instructions

1. **Server**: PHP 8.2+ with `pdo_mysql`, `mbstring`, `gd` or `imagick`,
   `zip`, `bcmath`; MySQL 8 or MariaDB 10.6+; Nginx or Apache; a process
   manager (systemd/Supervisor) if you add queue workers later.
2. **Deploy the code** (git pull or CI artifact) to e.g. `/var/www/pfa-sms`,
   with the webserver document root pointed at `public/`.
3. **Install dependencies for production**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```
4. **Environment**: copy `.env.example` to `.env`, set `APP_ENV=production`,
   `APP_DEBUG=false`, a real `APP_URL` (https), and production database
   credentials. Run `php artisan key:generate` once.
5. **Migrate & seed** (seed only on first deploy, and only the
   `RolePermissionSeeder` + `SchoolSettingSeeder` + `GradingScaleSeeder` +
   `AssessmentTypeSeeder` — skip `UserAndPeopleSeeder` and the other sample
   -data seeders in production; create real staff/admin accounts by hand
   or via `php artisan tinker`):
   ```bash
   php artisan migrate --force
   php artisan storage:link
   ```
6. **Cache for performance**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```
   Re-run these after every deploy; clear them (`php artisan optimize:clear`)
   before running further `artisan` commands during a deploy.
7. **File storage**: `storage/app/private` (student documents) must stay
   outside the public webroot. `storage/app/public` (logos, photos,
   signatures) is served via the `public/storage` symlink — make sure the
   symlink survives deploys (recreate with `storage:link` if your deploy
   process replaces the `public/` directory).
8. **HTTPS**: terminate TLS at the load balancer/webserver and set
   `SESSION_SECURE_COOKIE=true` in production.
9. **Queues/cron**: none are required for the current feature set, but if
   you add email/SMS notifications later, wire up a queue worker and the
   scheduler (`* * * * * php artisan schedule:run`).

---

## 11. Backup recommendations

- **Database**: nightly `mysqldump` (or your managed DB provider's
  automated backups) with at least 14–30 days of retention; test restores
  periodically, not just that the dump completes.
- **File storage**: back up `storage/app/public` (branding/photos) and
  `storage/app/private` (student documents) alongside the database —
  a DB-only backup loses uploaded files and signatures.
- **`.env`**: keep a secure, encrypted copy of production secrets outside
  the repository (a password manager or secrets vault) — it is
  intentionally never committed.
- **Before risky operations** (mass promotions, term closing/reopening,
  bulk fee assignment), take an ad-hoc DB snapshot — these are exactly the
  operations the audit log and `enrollments` history are designed to make
  recoverable from, but a backup is still cheaper than reconstructing data.

## 12. Security recommendations

- Rotate the seeded development passwords (section 4) immediately in any
  non-local environment, or better, delete those accounts and create real
  ones.
- Keep `APP_DEBUG=false` in production — debug mode leaks stack traces and
  environment details.
- Enforce strong passwords for staff accounts (the app currently requires
  8+ characters on creation; consider adding a password policy package if
  your school's compliance needs require more).
- Student documents are stored on the `local` (private) disk and served
  only through an authenticated, policy-checked download route — never
  move them to the `public` disk.
- Review the `audit_logs` table periodically, especially for `Result`
  status changes, `Payment` records, and `User` role changes.
- Keep Composer/npm dependencies current (`composer audit`, `npm audit`)
  and subscribe to Laravel security releases.
- Put the app behind HTTPS everywhere in production and set
  `SESSION_SECURE_COOKIE=true`.
- The default Laravel rate limiting on the `web` middleware group and
  Breeze's login throttling are enabled; consider adding stricter
  rate limits on the login route for public-facing deployments.

## 13. Known limitations

- Assumes a single school/campus (per the brief) — the schema does not yet
  support multi-tenant/multi-campus separation.
- No online payment gateway integration (Paystack/Flutterwave etc.) —
  payments are recorded by staff after the fact (cash/transfer/POS).
- No SMS/email/WhatsApp notifications yet — announcements are in-app only.
- `student_subjects` (elective subject selection per student) exists in the
  schema and model layer but has no dedicated UI yet; subjects are
  currently assigned at the class level via `class_subject`.
- Report cards show one term at a time; a cumulative/annual report card
  view is not yet built.
- No REST API — the app is server-rendered Blade only.

---

## Tech stack

- **Backend**: Laravel 13, PHP 8.4, MySQL
- **Frontend**: Blade, Tailwind CSS, Alpine.js
- **Auth**: Laravel Breeze
- **Authorization**: spatie/laravel-permission + Laravel Policies
- **PDF**: barryvdh/laravel-dompdf
