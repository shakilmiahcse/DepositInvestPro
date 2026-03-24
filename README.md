<p align="center">
  <h1 align="center">Coop Fund Manager</h1>
  <p align="center">
    A Laravel-based Cooperative Fund & Investment Management System
  </p>
</p>

---

## 🚀 About This Project

**Coop Fund Manager** is a Laravel-based web application designed to manage a **cooperative society's member deposits and investment system**.

This project is customized from Credit Lite, where all **loan and interest features have been removed**, and the focus is on:

- Member-based monthly deposits  
- Multi-account per member  
- Investment tracking  
- Profit distribution  
- Financial transparency  

---

## 🎯 Core Features

### 👥 Member & Account Management
- Each member can have **multiple accounts**
- Account-based deposit system
- Member profile with financial summary

### 💰 Monthly Deposit System
- Fixed **monthly deposit per account**
- Automatic monthly deposit generation
- Admin can mark deposits as **paid**
- Track **due vs paid deposits**

### 📊 Investment Management
- Record multiple investments
- Track invested amount
- Monitor returns and profits

### 📈 Profit Distribution
- Distribute profit among members
- Based on their total deposits
- Maintain distribution history

### 📑 Reports & Dashboard
- Total fund overview
- Member-wise deposit summary
- Investment & profit reports

---

## ⚙️ System Customization

This project has been customized with the following changes:

- ❌ Removed Loan Management  
- ❌ Removed Interest Calculation  
- ❌ Disabled Installment System  
- ✅ Added Monthly Deposit Module  
- ✅ Added Investment Tracking  
- ✅ Added Profit Distribution Logic  

---

## 🛠️ Tech Stack

- Laravel (PHP Framework)
- MySQL Database
- Blade Template Engine
- Bootstrap (Admin UI)

---

## 📦 Installation

```bash
git clone https://github.com/your-username/coop-fund-manager.git
cd coop-fund-manager

composer install
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate

# Run server
php artisan serve
