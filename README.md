# 🏢 EstatePulse CRM — Enterprise Real Estate Sales & Pipeline Suite

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Bootstrap 5.3](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![MySQL/MariaDB](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](LICENSE)

**EstatePulse CRM** is an enterprise-grade, high-performance Customer Relationship Management system custom-tailored for real estate developers, brokers, and sales agencies. Built with modern corporate aesthetics, it solves real-world realty sales bottlenecks: tracking multi-touch ad campaigns across 37+ parameters, enforcing role-based permissions, maintaining deep field-level audit diffs, conducting site visits, and accelerating sales workflows with 1-click WhatsApp automation.

---

## 📑 Table of Contents

- [Key Features & Highlights](#-key-features--highlights)
- [System Architecture & 37+ Lead Parameters](#-37-lead-parameters-breakdown)
- [Security & Role-Based Access Control (RBAC)](#-security--role-based-access-control-rbac)
- [Audit Trail vs Edit Logs](#-two-dedicated-tracking-systems)
- [Tech Stack & Prerequisites](#-tech-stack--prerequisites)
- [Installation & Quick Start](#-installation--quick-start)
- [Default Login Credentials](#-default-login-credentials)
- [Key Workflows & Modules](#-key-workflows--modules)
- [REST API & Webhook Ingestion](#-rest-api--webhook-ingestion)
- [Directory Structure](#-directory-structure)

---

## 🌟 Key Features & Highlights

### 1. 📊 Corporate Executive Dashboard
- **Real-Time KPIs**: Total Leads, Hot Prospects, Visits Scheduled, Revenue Pipeline, and Won Deals.
- **Pipeline Funnel Ribbon**: Interactive visual stage breakdown with active lead counters.
- **Priority Follow-up Queue**: Instant visibility into overdue and today's scheduled calls with 1-click dial and WhatsApp actions.
- **Attribution Breakdown**: Acquisition source doughnut charts analyzing channel ROI.

### 2. ⚡ Zero-Scroll Lead Management Directory
- **100% Screen-Fitted**: High data density layout with strict zero-horizontal-scrolling CSS.
- **6 Consolidated Data Columns**: Prospect & Contact, Requirement & Budget, Acquisition Source, Pipeline Stage, Assigned Executive, and Next Action.
- **Fast Action Modals**: 1-click Quick Status switcher, direct calling, and templated WhatsApp triggers.

### 3. 📲 1-Click WhatsApp Quick Templates
- **Zero Typing Overhead**: Preset templates dynamically interpolated with Customer Name, Project Name, Configuration, and Sales Executive details:
  1. *Project Brochure & Digital Walkthrough*
  2. *Site Visit Confirmation (with Location Google Pin)*
  3. *Cost Sheet & Payment Plan Schedule*
  4. *Gentle Follow-up & Availability Check*
- **Live Customization**: Edit text directly before launching WhatsApp Web.

### 4. 🏡 Property Site Visits & Outcome Logging
- **Visit Scheduling**: Tag project, set date/time, escorting executive, and flag cab assistance requests.
- **Outcome & Sentiment Logging**: Record whether visit was *Conducted*, *Rescheduled*, or *Cancelled/No-show*.
- **5-Star Sentiment Rating**: Track customer purchasing intent (Hot/Ready to Book down to Rejected).
- **Client Feedback**: Store detailed customer objections, unit preferences, and automatically advance pipeline stages.

### 5. 🔔 Header Overdue Follow-ups Notification Bell
- **Live Alert Counter**: Top header bell icon dynamically counting overdue and pending calls for the logged-in agent.
- **Quick-Jump Dropdown**: View client names, contact numbers, and relative time elapsed with 1-click jump to the lead profile.

### 6. 👥 Bulk Lead Reassignment & Stage Management
- **Batch Actions for Admins**: Checkbox selection for individual leads or entire pages.
- **1-Click Executive Reassignment**: Reassign large lead batches to active executives in seconds.
- **Bulk Stage Shift**: Advance multiple leads simultaneously, automatically writing audit logs for each record.

### 7. 📥 CSV Export & Import Suite
- **Filtered Export**: Export leads matching active search and filter parameters.
- **Sample Template**: Download pre-formatted CSV files with sample column headers.
- **Round-Robin Import**: Upload raw CSVs with auto-distribution to active sales agents.

---

## 📋 37+ Lead Parameters Breakdown

Every lead in EstatePulse CRM captures complete multi-channel attribution and realty specifications organized across 4 structured cards:

| Category | Parameters Captured |
| :--- | :--- |
| **1. Contact & Demographics** | Full Name, Primary Phone, Phone Country Code, Alternate Phone, Email, City, Physical Location, Preferred Location, Client IP Address, IP Country Code |
| **2. Property & Requirements** | Enquiry Title/Reference, Master Project Tag, Sub-Project/Tower/Unit, Property Type (Apartment, Villa, Plot, Commercial), Configuration (1, 2, 3, 4+ BHK, Penthouse), Min Budget, Max Budget, Sourcing Price, Requirement Description, Internal Notes |
| **3. Digital Marketing & Attribution** | Ad Platform (Meta, Google, MagicBricks, Housing), Source Category, Source Key, Sub-Source, Campaign Name, Campaign ID, Ad Set Name, Ad Creative Name, Ad ID, Search Keyword, Device/OS, UTM Source, UTM Medium, UTM Campaign, UTM Term, UTM Content, Google Click ID (GCLID), Landing Page URL |
| **4. CRM Lifecycle & Operations** | Current Pipeline Stage, Assigned Sales Executive, Ingestion Origin (API/Webhook/Manual), Next Follow-up Reminder, Hot Priority Flag, Organic Lead Flag |

---

## 🛡️ Security & Role-Based Access Control (RBAC)

The application enforces a multi-tier authorization hierarchy across all controllers and Blade views:

```
┌────────────────────────────────────────────────────────┐
│                   Admin Manager                        │  Full access, user administration,
│                                                        │  bulk reassign, lead deletion
└──────────────────────────┬─────────────────────────────┘
                           │
┌──────────────────────────▼─────────────────────────────┐
│                      Manager                           │  Full visibility, parameter editing,
│                                                        │  agent reassignment
└──────────────────────────┬─────────────────────────────┘
                           │
┌──────────────────────────▼─────────────────────────────┐
│                    Sales Agent                         │  Can only view & edit assigned leads;
│                                                        │  cannot reassign or delete
└──────────────────────────┬─────────────────────────────┘
                           │
┌──────────────────────────▼─────────────────────────────┐
│                    Telecaller                          │  Lead calling, follow-up logging;
│                                                        │  core parameters locked
└────────────────────────────────────────────────────────┘
```

- **UI Locks**: Unauthorized users see disabled action buttons and `"Edit Locked"` indicators.
- **Controller Protections**: Unauthorized `PUT`, `POST`, or `DELETE` requests trigger immediate rejection with descriptive alerts.
- **Reassignment Protection**: If an unauthorized user attempts to forge an `assigned_to` parameter, the controller strips the field.

---

## 🔍 Two Dedicated Tracking Systems

To guarantee total accountability and compliance, changes and events are separated into two distinct tabs:

### 🕒 Tab 4: Audit Trail (System Lifecycle History)
Records high-level operational milestones:
- Lead Ingestion (API webhook origin, timestamp, initial assignment)
- Pipeline Stage Transitions (e.g. *New Lead ➔ Site Visit Scheduled*)
- Follow-up conversations logged
- Property site visits scheduled and conducted

### 📝 Tab 5: Edit Logs (Field Modification Diffing)
Records precise field-by-field modifications whenever any user updates lead parameters:
- **Editor Info**: Name, Role Badge (Admin, Manager, Agent), Timestamp, and Client IP.
- **Field-by-Field Diff Table**:
  - **Modified Parameter**: Human-readable field title (e.g. *Max Budget*, *Internal Notes*, *Customer Name*).
  - **Original Value (Pehle)**: Displayed in red with strike-through `<del>`.
  - **Updated Value (Abhi)**: Displayed in bold green with formatted currency/status names.

---

## 💻 Tech Stack & Prerequisites

- **Backend**: Laravel 12.x, PHP 8.2+
- **Database**: MySQL 8.0+ or MariaDB 10.4+ (compatible with XAMPP, Laragon, Docker)
- **Frontend**: Blade, Bootstrap 5.3, Bootstrap Icons, Inter Typography
- **Dependencies**: Composer 2.x
- **Optional**: Node.js & NPM (for asset compilation)

---

## 🚀 Installation & Quick Start

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/crm_realestate.git
cd crm_realestate
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Open `.env` and configure your database settings:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_realestate
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Database Migrations & Seeds
Create the database in MySQL if it does not already exist, then run:
```bash
php artisan migrate --seed
```

### 6. Start the Local Server
```bash
php artisan serve --host=127.0.0.1 --port=8000
```
Open your browser and navigate to: **`http://127.0.0.1:8000`**

---

## 🔑 Default Login Credentials

The database seeder automatically initializes the following test accounts:

| Role | Email Address | Password | Privileges |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@realestate.com` | `admin123` | Full System Access, Team Management, Bulk Actions |
| **Sales Agent 1** | `rohan@realestate.com` | `agent123` | Manage Assigned Leads, Follow-ups, Visits |
| **Sales Agent 2** | `priya@realestate.com` | `agent123` | Manage Assigned Leads, Follow-ups, Visits |
| **Telecaller** | `priti@realestate.com` | `agent123` | Call Logging, View Follow-up Reminders |

---

## 📡 REST API & Webhook Ingestion

Ingest leads automatically from Facebook Ads, Google Ads Webhooks, MagicBricks, Housing.com, 99acres, or custom WordPress landing pages:

### Ingestion Endpoint
```http
POST /api/v1/leads
Content-Type: application/json
Accept: application/json
Authorization: Bearer <API_BEARER_TOKEN>
```

### Sample Payload
```json
{
  "name": "Rajesh Sharma",
  "phone": "9876543210",
  "email": "rajesh@example.com",
  "city": "Mumbai",
  "property_type": "Apartment",
  "configuration": "3 BHK",
  "budget_min": 150.0,
  "budget_max": 220.0,
  "project_name": "Emerald Heights",
  "source": "Meta Ads (FB/Insta)",
  "platform": "meta_ads",
  "campaign_name": "Mumbai_Luxury_Q4",
  "utm_source": "facebook",
  "utm_medium": "paid_social",
  "utm_campaign": "emerald_3bhk_leads",
  "is_hot": true
}
```

### Response
```json
{
  "status": "success",
  "message": "Lead ingested successfully.",
  "data": {
    "lead_id": 42,
    "name": "Rajesh Sharma",
    "assigned_to": 2,
    "assigned_agent": "Rohan Sharma"
  }
}
```

For full interactive API schemas and cURL/Python/PHP examples, log in and visit the in-app documentation: **`/api-documentation`**.

---

## 📁 Directory Structure

```
crm_realestate/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                     # REST API & Ingestion Controllers
│   │   │   └── Web/                     # Web Portal Controllers
│   │   │       ├── AuthController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── LeadImportExportController.php
│   │   │       ├── LeadWebController.php
│   │   │       ├── ProjectWebController.php
│   │   │       └── UserWebController.php
│   │   └── Middleware/                  # RBAC and Admin Guards
│   └── Models/                          # Eloquent Models & Business Logic
│       ├── Lead.php                     # 37+ Parameters & Relations
│       ├── LeadActivity.php             # System Audit & Diff JSON
│       ├── LeadFollowup.php             # Calls & Interaction Logs
│       ├── LeadSource.php
│       ├── LeadStatus.php               # Pipeline Stages & Color Coding
│       ├── Project.php                  # Property Inventory
│       ├── SiteVisit.php                # Scheduled Visits & Outcomes
│       └── User.php                     # RBAC Helpers & Roles
├── database/
│   ├── migrations/                      # Schema Migrations
│   └── seeders/                         # Realistic Realty Test Seeders
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php            # Master Layout (Inter, Bell, Search)
│       ├── auth/
│       │   └── login.blade.php          # 1-Click Role Login
│       ├── dashboard.blade.php          # KPI Metrics & Funnel Ribbons
│       ├── leads/
│       │   ├── index.blade.php          # Fluid Table & Bulk Actions
│       │   └── show.blade.php           # 37 Params, WhatsApp & Audit Tabs
│       ├── projects/
│       └── users/
└── routes/
    ├── api.php                          # REST Ingestion Endpoints
    └── web.php                          # Protected Web Portal Routes
```

---

## 📄 License

This software is released under the **MIT License**. You are free to modify and distribute it for personal or commercial real estate operations.
