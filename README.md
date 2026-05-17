# Dar El Sama ERP

[![GitHub Repo](https://img.shields.io/badge/GitHub-Repository-blue.svg?style=for-the-badge&logo=github)](https://github.com/nour195205/dar_elsama3_erp)

A comprehensive Enterprise Resource Planning (ERP) system tailored for Clinic Management, Human Resources, and Financial tracking. Built on the Laravel framework to provide a seamless user experience and precise resource management.

---

## 🌟 Core Features

### 👥 1. HR & Attendance Management
- Manage employees with diverse roles and permissions (Admin, Manager, Employee).
- **Smart QR Attendance System:** Employees scan a QR code from a display screen using their smartphones to check in and out.
- **Secure Device Pairing:** Prevents fraud by binding each employee's account to their personal device with a uniquely generated Device ID. Attendance cannot be recorded from unauthorized devices.
- **Automated Payroll Calculation:** Automatically calculates working hours based on check-in/out times and computes the earned salary based on the employee's configured Hourly Rate.

### 🏥 2. Clinic Management
- Comprehensive patient database including contact details.
- Manage the list of doctors and their specializations.
- Manage medical tests and test types.
- Delegate management and visit tracking.

### 💰 3. Finance Module
- Track Revenues and Expenses.
- Complete log of financial Transactions and treasury movements.
- Manage and issue Doctor Payouts based on percentages and operations.
- Comprehensive financial dashboard and reports for tracking profit and loss.

### 🔒 4. Advanced ACL (Access Control List)
- Permission Groups system for easy role assignment.
- Granular control at the Module level and Action level (Create, Edit, Delete).
- Detailed Activity Logs to track all system changes and the users who performed them, maintaining transparency and security.

---

## 🛠️ Tech Stack

| Technology | Description |
|------------|-------------|
| **Backend** | PHP 8.x / Laravel 11/12 |
| **Frontend** | Blade Templates, HTML5, Vanilla CSS |
| **Database** | MySQL |
| **Interactivity**| Vanilla JavaScript, AJAX, qrcode.js |
| **Styling** | Custom CSS Variables, FontAwesome Icons |

---

## 📂 System Architecture & UML

The `docs/uml/` directory contains engineering diagrams in **PlantUML** format to illustrate the project's architecture:

1. [Use Case Diagram](docs/uml/use_cases.puml)
2. [Database ER Diagram](docs/uml/database.puml)
3. [Class Diagram](docs/uml/class_diagram.puml)
4. [Component Diagram](docs/uml/component_diagram.puml)
5. [Attendance Sequence Diagram](docs/uml/attendance_flow.puml)
6. [Attendance Activity Diagram](docs/uml/activity_diagram.puml)
7. [Device Pairing State Diagram](docs/uml/state_diagram.puml)

---

## 🚀 Installation & Local Development

To set up the project in your local environment, follow these steps:

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/nour195205/dar_elsama3_erp.git
   cd dar_elsama3_erp
   ```

2. **Install Backend Dependencies:**
   ```bash
   composer install
   ```

3. **Configure the Environment:**
   Copy the `.env.example` file to `.env` and update your database connection details.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Prepare the Database:**
   ```bash
   php artisan migrate --seed
   ```

5. **Run the Development Server:**
   ```bash
   php artisan serve
   ```
   *The system will be available at `http://localhost:8000`*

---

## 🌐 Shared Hosting Notes

The system is optimized to run efficiently on shared hosting environments such as **InfinityFree**:

- **Public Routing Fix:** Ensure an `.htaccess` file exists in the Root Directory to forward all requests to the `/public` folder.
- **QR Screen 404 Fixes:** Ensure the relevant routes in `web.php` are defined as Public and exclude authentication Middleware.
- **Caching Mechanism:** The QR Code (Pairing) system relies on token caching. Ensure that `CACHE_DRIVER=file` is correctly configured in your hosting environment to prevent session loss.
- **View Updates:** When uploading changes to `Blade` files, make sure to clear the cached files in `storage/framework/views/` to apply updates.

---
*Developed as the ultimate solution for efficiently and easily managing Dar El Sama.*
