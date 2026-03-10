<p align="center"><a href="https://mueblegroup.com" target="_blank"><img src="https://mueblegroup.com/wp-content/uploads/2024/02/MUEBLE-LOGO-drak.png" width="400" alt="Mueble Logo"></a></p>

# Studio Management System (Laravel)

A modern, self-hosted **Studio / LMS Management System** built with Laravel.  
Designed for dance studios, gyms, and training centers to manage classes, students, payments, and attendance efficiently.

---

## 🚀 Features

### Core Management
- Admin, Teacher, and Student roles
- Secure authentication & role-based access
- Dashboard with key metrics (earnings, users, classes)

### Class & Booking System
- Individual classes (recurring & one-time)
- Monthly / yearly class plans
- Classcard system (fixed number of lessons with expiry)
- Attendance tracking & booking history

### Payments
- Stripe payment integration
- Order & payment records
- Automatic fulfillment after successful payment

### Communication
- Announcements for students & teachers
- Email notifications (via SMTP / PHPMailer)

### System & Developer Features
- Laravel MVC architecture
- Database migrations for safe deployments
- Environment-based configuration
- Optimized for self-hosted production environments

---

## 🛠 Tech Stack

- **Backend:** Laravel (PHP)
- **Database:** MySQL / MariaDB
- **Frontend:** Blade + Tailwind CSS
- **Payments:** Stripe
- **Server:** Linux (Nginx / Apache)
- **Version Control:** Git + GitHub

---

## 📦 Installation

### Requirements
- PHP 8.1+
- Composer
- MySQL / MariaDB
- Node.js & NPM (for assets)
- Web server (Nginx / Apache)

---

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/mueblegroup/MG-MS-Studio.git
cd MG-MS-Studio
```

### 2️⃣ Install Dependencies

```bash
composer install
npm install
npm run build
```

### 3️⃣ Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4️⃣ Database Setup

```bash
php artisan migrate
```

### 5️⃣ Storage & Permissions

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### 6️⃣ Run the Application

```bash
php artisan serve
```
- Production servers should use Nginx/Apache, not artisan serve.

### ⚠️ Important Notes
- .env files are not tracked in Git
- vendor/, node_modules/, logs, and cache are ignored
- All database changes must be done via migrations
- Do not edit production code directly

### ⚠️ Security
- Environment variables protect sensitive credentials
- Role-based permissions enforced at controller level
- CSRF protection enabled by default
- Payment handling delegated to Stripe

### 📄 License
- This project is proprietary and owned by Mueble Group.
- Unauthorized distribution or resale is not permitted.

### 👨‍💻 Maintained By
#### Mueble Group
#### Web Development · LMS Solutions · Digital Services
