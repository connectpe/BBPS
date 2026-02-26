<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <strong>BBPS Portal – Bharat Bill Payment System Integration</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-Framework-red" />
  <img src="https://img.shields.io/badge/BBPS-API-blue" />
  <img src="https://img.shields.io/badge/Status-Production%20Ready-success" />
</p>

---

## 📌 About BBPS Portal

The **BBPS Portal** is a secure and scalable web application built using **:contentReference[oaicite:0]{index=0}** that integrates the **:contentReference[oaicite:1]{index=1} (BBPS)** APIs to enable seamless bill payment and recharge services.

This portal allows users and administrators to perform real-time bill payments, recharges, validations, and transaction status checks across multiple biller categories through BBPS-compliant APIs.

---

## 🚀 Key Features

- 🔐 Secure BBPS API Authentication (Token-based)
- 💳 Bill Payment & Recharge Processing
- ✅ Real-time Bill Validation
- ⏳ Pending & Timeout Transaction Handling
- 🔁 Automatic Status Reconciliation
- 📊 Transaction Logs & Reporting
- 🧾 User & Admin Role Management
- ⚡ Optimized for High-Concurrency Usage
- 🛡️ Robust Error Handling & Logging

---

## 🧩 BBPS Services Integrated

- Electricity Bill Payment  
- Mobile Postpaid / Prepaid Recharge  
- DTH Recharge  
- Water Bill Payment  
- Gas Bill Payment  
- FASTag Recharge  
- Broadband / Landline Bills  

(All services are handled as per BBPS technical and security guidelines.)

---

## 🏗️ Application Architecture

- **Framework:** Laravel (MVC Architecture)
- **Backend:** PHP
- **Database:** MySQL
- **Authentication:** Token-based API Authentication
- **Queue & Jobs:** Laravel Queues (for async processing)
- **Caching:** Redis / File Cache (configurable)
- **Logging:** Laravel Log Channels
- **Deployment:** Shared / VPS Hosting (BBPS-ready)

---

## 🔄 Transaction Flow (BBPS)

1. **Token Generation**
   - Secure token generated using BBPS credentials
2. **Balance Check**
   - Wallet / account balance verification
3. **Bill Validation**
   - Fetch bill details from BBPS
4. **Payment Initiation**
   - Transaction request sent to BBPS
5. **Status Handling**
   - Success / Pending / Failed
6. **Reconciliation**
   - Auto status check & update for pending cases

---

## ⏱️ Pending & Timeout Handling

- Pending transactions are identified using BBPS response codes
- System periodically checks transaction status via Status API
- Retry mechanism is implemented for timeout scenarios
- Final status is updated automatically in the database
- Complete audit trail is maintained for compliance

---

## 📁 Logging & Auditing

The system maintains detailed logs for:

- Token Generation (Success / Failure)
- Balance Check APIs
- Validation APIs
- Payment APIs
- Status & Reconciliation APIs
- Error & Exception Handling

All logs include:
- Request URL
- Request Payload
- Response Data
- Timestamp

---

## ⚙️ Installation & Setup

```bash
git clone https://github.com/your-repo/bbps-portal.git
cd bbps-portal
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
