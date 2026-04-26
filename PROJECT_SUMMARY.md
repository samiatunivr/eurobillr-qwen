# Eurobillr - Enterprise SaaS Invoicing Platform

## Project Status: Core Modules Complete

### ✅ Completed Components

#### Database Schema (4 Migration Files)
- `001_core_tables.sql` - Users, workspaces, sessions, auth tokens
- `002_invoices.sql` - Invoices, line items, payments, recurring
- `003_dashboard_reports.sql` - Activity logs, notifications, reports
- `004_clients_expenses.sql` - Clients with EU VAT, Expenses with OCR

**Total Tables:** 20 production-ready tables with proper FK constraints and indexes

#### Core Framework (`/app/Core`)
- **Database.php** - Singleton PDO wrapper with prepared statements, transactions
- **SessionManager.php** - Secure DB-backed sessions with flash messages
- **Authentication.php** - Full auth system with 2FA/TOTP, rate limiting, account lockout
- **View.php** - Template engine with layouts and sections
- **Router.php** - Pattern-based routing with middleware support
- **Controller.php** - Base controller with auth, CSRF, workspace isolation

#### Models (`/app/Models`)
- **User.php** - User management with roles
- **Workspace.php** - Multi-tenant workspace handling
- **Invoice.php** - Invoice CRUD with line items, payments
- **Client.php** - Client management with EU VAT validation (all 27 EU countries)
- **Expense.php** - Expense tracking with OCR data storage, multi-currency

#### Services (`/app/Services`)
- **ClientService.php** - Business logic for clients with validation
- **ExpenseService.php** - Expense logic with OCR processing hooks
- **InvoiceService.php** - Invoice operations (from previous phase)

#### Controllers (`/app/Controllers`)
- **AuthController.php** - Login, register, 2FA, password reset
- **DashboardController.php** - Analytics and KPIs
- **InvoiceController.php** - Full invoice CRUD, PDF, email
- **ClientController.php** - Client management with search
- **ExpenseController.php** - Expense management with file uploads

#### Frontend Views
- **layouts/app.php** - Main layout with sidebar navigation
- **auth/** - Login, register, 2FA, password reset views
- **dashboard/index.php** - Interactive dashboard with Chart.js
- **invoices/** - List, create, edit, show views
- **clients/index.php** - Client listing with stats cards
- **expenses/index.php** - Expense listing with filters

#### Assets
- **css/app.css** - TailwindCSS compiled styles + custom components
- **js/app.js** - Vanilla JS utilities (tooltips, search, notifications)

---

### 🏗 Architecture Highlights

```
┌─────────────────────────────────────────────────────────────┐
│                     HTTP Request                             │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  Router → Controller → Service → Model → Database           │
│         ↓            ↓         ↓                              │
│      Auth        Validate  Business  Entity   Prepared       │
│      Check       Logic     Logic     Layer    Statements     │
└─────────────────────────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  Response: View (PHP template) or JSON API                  │
└─────────────────────────────────────────────────────────────┘
```

**Key Patterns Implemented:**
- ✅ MVC Architecture
- ✅ Repository Pattern (via Models)
- ✅ Service Layer for business logic
- ✅ DTO pattern (arrays with type hints)
- ✅ Dependency Injection ready
- ✅ PSR-4 Autoloading

---

### 🔒 Security Features

| Feature | Implementation |
|---------|---------------|
| SQL Injection | Prepared statements everywhere |
| XSS Protection | htmlspecialchars() on all output |
| CSRF Protection | Token validation on POST requests |
| Session Hijacking | Database-backed sessions with regeneration |
| Brute Force | Rate limiting + account lockout |
| Password Security | bcrypt cost 12 + timing-safe comparison |
| File Uploads | MIME type validation + size limits |
| Multi-tenant | Workspace ID on every query |
| Headers | CSP, X-Frame-Options, XSS-Protection |

---

### 🇪🇺 European Compliance

**VAT Validation:** All 27 EU country formats supported
- AT, BE, BG, HR, CY, CZ, DK, EE, FI, FR, DE, GR, HU, IE, IT, LV, LT, LU, MT, NL, PL, PT, RO, SK, SI, ES, SE

**Multi-Currency:** 
- EUR, USD, GBP, CHF, PLN, CZK, HUF, SEK, DKK, NOK
- Exchange rate storage per transaction
- Base currency conversion

**Peppol Ready:**
- Peppol ID field on clients
- UBL XML generation planned
- Document status tracking table

**GDPR:**
- Data export/delete methods ready
- Audit logging for all actions

---

### 📊 Key Features by Module

#### Clients
- [x] Full CRUD operations
- [x] EU VAT number validation
- [x] Peppol ID support
- [x] Multi-currency defaults
- [x] Search & filtering
- [x] Archive/restore
- [ ] EU company auto-lookup (API integration needed)

#### Expenses  
- [x] Full CRUD operations
- [x] Receipt file upload
- [x] OCR data storage (Amazon Textract ready)
- [x] Category management
- [x] Billable expense tracking
- [x] Multi-currency with conversion
- [x] Status workflow (pending→approved→paid)
- [ ] OCR extraction logic (placeholder ready)

#### Invoices (from Phase 3)
- [x] Full CRUD operations
- [x] Line items with tax/discount
- [x] PDF generation (TCPDF)
- [x] Email sending (Symfony Mailer)
- [x] Payment tracking
- [x] Credit notes support
- [x] Recurring invoices
- [ ] UBL 2.1 XML export
- [ ] Peppol send integration

---

### 🚀 Next Steps for Production

1. **Frontend Forms** - Create/edit views for clients and expenses
2. **PDF Templates** - Professional invoice/receipt PDFs
3. **OCR Integration** - Connect Amazon Textract API
4. **EU Company Lookup** - Integrate CBE/VIES APIs
5. **UBL Generator** - Build compliant XML exporter
6. **Peppol Access Point** - Partner integration for e-invoicing
7. **Payment Gateways** - Stripe/Mollie integration
8. **Email Queue** - Redis-based job processing
9. **Testing Suite** - PHPUnit tests for all services
10. **Deployment** - Docker Compose + CI/CD pipeline

---

### 📁 Project Structure

```
/workspace
├── app/
│   ├── Config/           # Application configuration
│   ├── Controllers/      # HTTP request handlers
│   ├── Core/             # Framework foundation
│   ├── Middleware/       # Request middleware
│   ├── Models/           # Data access layer
│   └── Services/         # Business logic layer
├── database/
│   └── migrations/       # SQL schema files
├── public/
│   ├── css/              # Compiled CSS
│   ├── js/               # JavaScript bundles
│   ├── uploads/          # User files
│   └── index.php         # Application entry point
├── views/
│   ├── auth/             # Authentication views
│   ├── clients/          # Client views
│   ├── dashboard/        # Dashboard views
│   ├── expenses/         # Expense views
│   ├── invoices/         # Invoice views
│   └── layouts/          # Layout templates
├── storage/
│   ├── logs/             # Application logs
│   └── cache/            # Cache files
└── vendor/               # Composer dependencies
```

---

### 💻 Tech Stack Summary

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.3 |
| Database | MySQL 8.0 / MariaDB 10.6 |
| CSS Framework | TailwindCSS 3.x |
| Charts | Chart.js 4.x |
| PDF | TCPDF |
| Mail | Symfony Mailer + Mime |
| Sessions | Database-backed |
| Auth | Custom with 2FA/TOTP |
| Architecture | MVC + Service Layer |

**Deliberately NOT using:** Laravel, Symfony Framework, CodeIgniter, or any full-stack framework.

---

### 📈 Performance Targets

- Page load: < 2 seconds
- Query time: < 100ms average
- Concurrent users: 10,000+ per workspace cluster
- Data isolation: Strict tenant separation via workspace_id

---

*Built for European SMEs, freelancers, and agencies requiring professional invoicing with Peppol e-invoicing compliance.*
