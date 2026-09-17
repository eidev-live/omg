# Product Requirements Document (PRD)
## Oh My Egg — Egg Sales & Inventory Management System

**Version:** 1.0  
**Product Name:** Oh My Egg  
**System Type:** Web-based Egg Sales & Inventory Management  
**Primary Stack:** Laravel + Inertia.js + Vue 3 + SQLite + Vite  
**UI Language:** Bahasa Indonesia  
**Target:** Personal / Small Business / Open Source Project

---

# 1. Product Overview

**Oh My Egg** adalah aplikasi web sederhana untuk membantu pencatatan bisnis penjualan telur, mulai dari:

1. Pencatatan pembelian telur
2. Pengelolaan stock
3. Pengelolaan harga jual
4. Pencatatan customer
5. Transaksi penjualan
6. Monitoring pembayaran
7. Monitoring pengiriman
8. Perhitungan HPP menggunakan metode FIFO
9. Perhitungan revenue, COGS, gross profit, dan margin
10. Dashboard bisnis
11. Reporting

Aplikasi dirancang agar mudah digunakan oleh pemilik usaha kecil tanpa membutuhkan pengetahuan akuntansi atau inventory management.

---

# 2. Product Brand

## Brand Name

**Oh My Egg**

Brand harus terasa:

- friendly
- memorable
- sederhana
- modern
- tidak terlalu corporate
- mudah disebut
- cocok untuk aplikasi maupun bisnis telur

## Brand Personality

Gunakan karakter:

> Simple, Fresh, Friendly, Clean, Practical.

## Color Direction

Dominan:

- White — background utama
- Yellow — egg / highlight / accent
- Orange — CTA / important action
- Green — success / payment / delivery / profit

Gunakan warna secara proporsional.

### Recommended palette

```text
Primary Yellow
#F9C74F

Primary Orange
#F9844A

Success Green
#43AA8B

Dark Green
#277A63

Background
#FFFDF7

White
#FFFFFF

Text
#263238

Muted
#6B7280

Border
#E5E7EB

Danger
#E76F51
```

Jangan menggunakan seluruh warna secara berlebihan.

**White harus tetap menjadi warna dominan.**

Yellow/orange digunakan untuk branding dan CTA.

Green digunakan terutama untuk:

- paid
- delivered
- profit
- success
- completed

Red hanya untuk:

- error
- destructive action
- warning serius

---

# 3. UI / UX Design Principles

Design harus:

- clean
- modern
- responsive
- mobile friendly
- desktop friendly
- mudah dipahami user non-technical
- memiliki visual hierarchy yang jelas
- konsisten antar halaman

Hindari:

- dashboard terlalu ramai
- terlalu banyak warna
- tabel terlalu kompleks
- modal bertingkat
- animasi berlebihan
- istilah teknis
- informasi yang tidak diperlukan

---

# 4. Global Layout

Desktop:

```text
┌────────────────────────────────────────────────────┐
│ Oh My Egg                         User / Profile   │
├──────────────┬─────────────────────────────────────┤
│              │                                     │
│ Dashboard    │                                     │
│ Penjualan    │          Main Content               │
│ Pembelian    │                                     │
│ Stock        │                                     │
│              │                                     │
│ Master       │                                     │
│  Customer    │                                     │
│  Type Jual   │                                     │
│              │                                     │
│ Laporan      │                                     │
│              │                                     │
│ Settings     │                                     │
│              │                                     │
└──────────────┴─────────────────────────────────────┘
```

Mobile:

- sidebar menjadi hamburger menu
- table berubah menjadi responsive card/list jika diperlukan
- form menggunakan single-column layout
- CTA utama tetap mudah dijangkau

---

# 5. Navigation

Menu utama:

```text
Dashboard

Transactions
├── Penjualan
└── Pembelian

Inventory
└── Stock

Master
├── Customer
└── Type Penjualan

Reports
├── Penjualan
├── Pembelian
├── Profit
└── Stock

Settings
└── Profile
```

Jika diperlukan:

```text
Logout
```

---

# 6. Authentication

System wajib memiliki authentication.

Minimum:

- Login
- Logout
- Session management
- Password hashing
- Remember me
- CSRF protection
- Rate limiting login
- Session regeneration setelah login
- Authorization middleware

User tidak boleh mengakses halaman internal sebelum login.

Route:

```text
/login
```

Protected routes:

```text
/dashboard
/sales/*
/purchases/*
/stock/*
/customers/*
/sale-types/*
/reports/*
/profile
```

---

# 7. User Account

Untuk MVP, system menggunakan single-owner/small-business authentication.

Table:

```text
users
```

Minimal:

```text
id
name
email
password
created_at
updated_at
```

Profile:

- Name
- Email
- Change Password

Password:

- minimum 8 characters
- hashed menggunakan Laravel Hash
- jangan pernah disimpan plaintext

---

# 8. Security Requirements

Implementasikan security best practice Laravel.

## Authentication

- Laravel authentication
- hashed password
- session regeneration
- logout invalidates session

## Authorization

Semua business route harus menggunakan:

```text
auth
```

middleware.

## CSRF

Semua form mutating request harus menggunakan CSRF protection.

## Validation

Semua input harus divalidasi server-side.

Jangan hanya mengandalkan validation Vue.

## Mass Assignment

Gunakan:

```php
$fillable
```

atau:

```php
$guarded
```

dengan benar.

## SQL Injection

Gunakan Laravel Eloquent / Query Builder.

Jangan membuat raw SQL dengan string interpolation.

## XSS

Output user-generated content harus di-escape.

## Rate Limiting

Login endpoint harus memiliki rate limiting.

## Session

Setelah authentication:

```text
session()->regenerate()
```

## Sensitive Data

Jangan menyimpan:

- password plaintext
- secret key
- API key
- `.env`

ke GitHub.

---

# 9. Database

Database:

**SQLite**

File:

```text
database/database.sqlite
```

Jangan commit production database ke repository.

Gunakan:

```text
database/database.sqlite.example
```

atau migration + seeder.

---

# 10. Database Entities

Core tables:

```text
users

customers

sale_types

purchases

sales

sale_items

stock_movements
```

Optional future:

```text
suppliers
payments
expenses
```

---

# 11. Customer

Table:

```text
customers
```

Fields:

```text
id
name
phone
location
notes
is_active
created_at
updated_at
deleted_at
```

Use soft delete jika memungkinkan.

Customer dapat:

- dibuat
- diedit
- dinonaktifkan
- dicari

Customer yang pernah digunakan dalam transaksi tidak boleh benar-benar dihapus jika hal tersebut merusak histori transaksi.

---

# 12. Sale Type

Master type penjualan.

Contoh:

```text
Pack
10 telur
Rp33.000

Tray
30 telur
Rp91.000

Ikat
180 telur
Rp510.000
```

Table:

```text
sale_types
```

Fields:

```text
id
name
egg_quantity
selling_price
is_active
created_at
updated_at
```

Rules:

```text
egg_quantity > 0
selling_price >= 0
name required
```

Sale type inactive:

- tidak muncul untuk transaksi baru
- tetap muncul pada histori transaksi

---

# 13. Purchase

Purchase adalah sumber utama penambahan stock.

Contoh:

```text
Tanggal       : 16 September 2026
Jumlah Ikat   : 1
Isi           : 180 telur
Total Cost    : Rp475.000
HPP           : Rp2.638,89/telur
```

Table:

```text
purchases
```

Fields:

```text
id
purchase_number
purchase_date
quantity
egg_quantity
total_cost
cost_per_egg
notes
created_at
updated_at
```

Formula:

```text
cost_per_egg =
total_cost / egg_quantity
```

---

# 14. FIFO Inventory Costing

System menggunakan:

# FIFO — First In First Out

Stock telur dianggap keluar berdasarkan batch pembelian paling lama.

Contoh:

Purchase #001:

```text
100 eggs
HPP Rp2.500
```

Purchase #002:

```text
100 eggs
HPP Rp2.800
```

Stock:

```text
200 eggs
```

Kemudian menjual:

```text
120 eggs
```

FIFO:

```text
100 × Rp2.500
+
20 × Rp2.800
```

COGS:

```text
Rp250.000 + Rp56.000
= Rp306.000
```

Remaining stock:

```text
80 eggs
@ Rp2.800
```

---

# 15. FIFO Stock Layer

Untuk implementasi FIFO yang benar, jangan hanya menyimpan stock total.

Gunakan konsep inventory layer.

Recommended table tambahan:

```text
inventory_layers
```

Fields:

```text
id
purchase_id
quantity_received
quantity_remaining
unit_cost
created_at
updated_at
```

Contoh:

```text
Layer #1
Purchase 001
Received: 180
Remaining: 50
Cost: 2.638,89

Layer #2
Purchase 002
Received: 180
Remaining: 180
Cost: 2.777,78
```

Stock:

```text
50 + 180 = 230 eggs
```

---

# 16. Stock Movement

Table:

```text
stock_movements
```

Fields:

```text
id
movement_date
movement_type
quantity
reference_type
reference_id
notes
created_at
updated_at
```

Movement type:

```text
PURCHASE
SALE
ADJUSTMENT_IN
ADJUSTMENT_OUT
```

Positive quantity:

```text
stock masuk
```

Negative quantity:

```text
stock keluar
```

Stock movement digunakan untuk audit.

---

# 17. Stock Calculation

Current stock:

```text
SUM(stock_movements.quantity)
```

atau melalui inventory layer:

```text
SUM(inventory_layers.quantity_remaining)
```

Kedua hasil harus konsisten.

Jika tidak konsisten, system harus memberikan warning pada internal validation/report.

---

# 18. Stock Adjustment

User dapat melakukan adjustment.

Contoh:

```text
System Stock: 100
Physical Stock: 98
```

User membuat:

```text
Adjustment Out
Quantity: 2
Reason: Telur pecah
```

System:

```text
Stock = 98
```

Adjustment wajib memiliki:

```text
reason
```

Adjustment tidak boleh mengubah histori purchase/sales.

---

# 19. Sales Transaction

Sales terdiri dari:

```text
sales
sale_items
```

## Sales

Fields:

```text
id
invoice_number
sale_date
customer_id
total_amount
payment_status
paid_amount
payment_date
delivery_status
delivered_at
notes
created_at
updated_at
```

## Sale Items

Fields:

```text
id
sale_id
sale_type_id
sale_type_name
quantity
egg_quantity
unit_price
subtotal
unit_cost
total_cost
profit
created_at
updated_at
```

---

# 20. Price Snapshot

Saat transaksi dibuat, harga master harus disalin ke sale item.

Contoh:

Master:

```text
Pack = Rp33.000
```

Transaction:

```text
2 × Pack
unit_price = Rp33.000
subtotal = Rp66.000
```

Jika master berubah:

```text
Pack = Rp35.000
```

transaksi lama tetap:

```text
Rp66.000
```

Jangan mengambil harga transaksi lama secara realtime dari master.

---

# 21. Multi Item Sales

Satu transaksi boleh memiliki beberapa item.

Contoh:

```text
Budi

2 × Pack
Rp66.000

1 × Tray
Rp91.000

Total:
Rp157.000
```

Egg quantity:

```text
2 × 10 + 1 × 30
= 50 eggs
```

System harus memvalidasi stock:

```text
Available Stock >= 50
```

Jika tidak cukup:

```text
Transaksi tidak dapat diproses.
Stock telur tidak mencukupi.
```

---

# 22. Sales FIFO Processing

Ketika sales disimpan:

1. Hitung total egg quantity
2. Validasi stock
3. Ambil inventory layer tertua
4. Kurangi quantity_remaining
5. Jika layer habis, lanjut ke layer berikutnya
6. Hitung COGS setiap layer
7. Total COGS menjadi `total_cost`
8. Simpan cost pada sale item
9. Hitung profit
10. Buat stock movement SALE

Semua proses tersebut harus berada dalam:

```text
Database Transaction
```

Jika satu proses gagal:

```text
ROLLBACK
```

Tidak boleh terjadi kondisi:

```text
Sales berhasil
tetapi stock gagal dikurangi
```

---

# 23. FIFO Example

Stock:

```text
Layer 1
50 eggs
@ Rp2.500

Layer 2
100 eggs
@ Rp2.800
```

Customer membeli:

```text
70 eggs
```

System mengambil:

```text
50 × 2.500
= 125.000

20 × 2.800
= 56.000
```

COGS:

```text
181.000
```

Remaining:

```text
Layer 1 = 0
Layer 2 = 80
```

---

# 24. Profit Calculation

Revenue:

```text
SUM(sale_items.subtotal)
```

COGS:

```text
SUM(sale_items.total_cost)
```

Gross Profit:

```text
Revenue - COGS
```

Profit Margin:

```text
Gross Profit / Revenue × 100
```

Jika Revenue = 0:

```text
Margin = 0
```

---

# 25. Payment

Payment status:

```text
UNPAID
PARTIAL
PAID
```

Fields:

```text
payment_status
paid_amount
payment_date
```

Rules:

```text
paid_amount <= total_amount
paid_amount >= 0
```

Outstanding:

```text
total_amount - paid_amount
```

Jika:

```text
paid_amount = 0
```

status:

```text
UNPAID
```

Jika:

```text
0 < paid_amount < total_amount
```

status:

```text
PARTIAL
```

Jika:

```text
paid_amount = total_amount
```

status:

```text
PAID
```

---

# 26. Delivery

Delivery status:

```text
PENDING
SHIPPED
DELIVERED
```

Flow:

```text
PENDING
   ↓
SHIPPED
   ↓
DELIVERED
```

Fields:

```text
delivery_status
delivered_at
```

Untuk MVP, tidak perlu membuat shipping management yang kompleks.

---

# 27. Dashboard

Dashboard harus memberikan ringkasan bisnis secara cepat.

Top KPI:

```text
Total Penjualan
Uang Masuk
Piutang
Total Pembelian
HPP / COGS
Gross Profit
Stock Telur
```

Filter:

```text
Hari Ini
Minggu Ini
Bulan Ini
Tahun Ini
Custom Date Range
```

---

# 28. Dashboard Cards

Contoh:

```text
┌─────────────────┐
│ Total Penjualan │
│ Rp 6.820.000    │
│ 32 transaksi    │
└─────────────────┘

┌─────────────────┐
│ Uang Masuk      │
│ Rp 6.100.000    │
└─────────────────┘

┌─────────────────┐
│ Gross Profit    │
│ Rp 2.470.000    │
└─────────────────┘

┌─────────────────┐
│ Stock Telur     │
│ 320 butir       │
└─────────────────┘
```

---

# 29. Dashboard Charts

Gunakan chart sederhana.

## Sales Trend

X:

```text
Date
```

Y:

```text
Revenue
```

## Profit Trend

X:

```text
Date
```

Y:

```text
Gross Profit
```

Jangan menggunakan chart terlalu banyak.

---

# 30. Dashboard Alerts

Contoh:

### Outstanding Payment

```text
5 transaksi belum lunas
Total Rp720.000
```

### Pending Delivery

```text
3 transaksi belum dikirim
```

### Low Stock

Jika stock <= configured minimum:

```text
Stock telur tersisa 25 butir.
```

Minimum stock dapat diletakkan di settings.

---

# 31. Sales Page

Page:

```text
Penjualan
```

Elements:

```text
[ + Transaksi Penjualan ]

Search
Date Filter
Payment Filter
Delivery Filter
```

Table:

```text
Invoice
Tanggal
Customer
Total
Payment
Delivery
Action
```

Action:

```text
View
Edit
```

Untuk transaksi yang sudah final, hindari perubahan yang dapat merusak inventory.

---

# 32. Sales Form

Form:

```text
Tanggal
Customer
```

Items:

```text
Type Penjualan
Qty
Harga
Subtotal
```

Button:

```text
+ Tambah Item
```

Summary:

```text
Total Egg
Total Harga
Stock Available
```

Payment:

```text
Payment Status
Paid Amount
Payment Date
```

Delivery:

```text
Delivery Status
```

CTA:

```text
Simpan Transaksi
Batal
```

---

# 33. Purchase Page

List:

```text
Tanggal
Purchase Number
Jumlah
Egg Quantity
Total Cost
HPP / Egg
```

Create:

```text
Tanggal
Jumlah ikat
Egg per ikat
Total Cost
Notes
```

System menghitung:

```text
Total Eggs
Cost per Egg
```

Purchase otomatis menghasilkan:

```text
Inventory Layer
Stock Movement
```

---

# 34. Stock Page

Display:

```text
Current Stock
```

Contoh:

```text
230 butir
```

Inventory layers:

```text
Batch
Tanggal
Original Qty
Remaining Qty
HPP
```

Stock movement:

```text
Tanggal
Type
Qty
Reference
Notes
```

---

# 35. Customer Page

Table:

```text
Customer
Phone
Location
Total Transactions
Total Purchase
Status
```

Customer detail:

```text
Profile
Transaction History
Total Spending
Outstanding
```

---

# 36. Reports

## Sales Report

Filter:

```text
Date
Customer
Payment
Delivery
```

Columns:

```text
Invoice
Date
Customer
Revenue
Paid
Outstanding
Status
```

## Purchase Report

```text
Date
Purchase
Egg Quantity
Total Cost
Cost/Egg
```

## Profit Report

```text
Date
Revenue
COGS
Gross Profit
Margin
```

## Stock Report

```text
Current Stock
Stock In
Stock Out
Adjustment
Inventory Value
```

Export CSV dapat dimasukkan jika mudah.

PDF tidak wajib untuk MVP.

---

# 37. Inventory Value

Karena menggunakan FIFO:

Inventory value harus dihitung dari remaining inventory layers.

Contoh:

```text
Layer 1
50 × Rp2.500
= Rp125.000

Layer 2
80 × Rp2.800
= Rp224.000

Inventory Value
= Rp349.000
```

Jangan menggunakan:

```text
Current Stock × latest purchase price
```

karena tidak merepresentasikan FIFO.

---

# 38. Data Integrity

System harus mencegah:

- sale melebihi stock
- paid amount melebihi invoice
- negative quantity
- negative price
- negative stock
- menghapus purchase yang sudah digunakan sales
- menghapus sale yang sudah memengaruhi inventory tanpa proses reversal

Jika transaksi perlu dibatalkan, gunakan mekanisme:

```text
VOID / CANCEL
```

dan buat reversal stock movement jika diperlukan.

---

# 39. Transaction Editing

Untuk menjaga FIFO dan audit trail:

### Draft transaction

Boleh diedit.

### Completed transaction

Jangan bebas diedit.

Jika sale sudah memengaruhi stock:

```text
Edit quantity
Edit item
Edit customer
```

harus memiliki mekanisme inventory recalculation/reversal.

Untuk MVP, pilihan paling aman:

> **Transaksi penjualan yang sudah tersimpan tidak dapat mengubah item/quantity.**

Jika salah:

```text
Cancel Transaction
+
Create New Transaction
```

Ini jauh lebih aman untuk versi pertama.

---

# 40. Purchase Editing

Purchase yang sudah menghasilkan inventory layer tidak boleh bebas diedit.

Jika salah:

```text
Cancel / Reverse Purchase
```

dan inventory dikoreksi melalui transaction mechanism.

Untuk MVP:

> Purchase yang sudah digunakan oleh sales tidak boleh dihapus.

---

# 41. Audit Trail

Tambahkan field:

```text
created_at
updated_at
```

Untuk transaksi penting, jika memungkinkan tambahkan:

```text
created_by
updated_by
```

Future:

```text
activity_logs
```

untuk mencatat:

```text
User created sale
User cancelled sale
User adjusted stock
User changed sale type
```

---

# 42. Error Handling

User harus mendapatkan error yang jelas.

Jangan:

```text
SQLSTATE[23000]...
```

Tampilkan:

```text
Transaksi tidak dapat disimpan.

Stock telur tidak mencukupi.
Stock tersedia: 20 butir
Kebutuhan: 30 butir
```

Untuk system error:

- log error ke Laravel log
- tampilkan generic message ke user
- jangan expose stack trace di production

---

# 43. Empty State

Setiap halaman harus memiliki empty state.

Contoh:

```text
Belum ada transaksi penjualan.

Mulai catat penjualan pertama Anda.

[ + Tambah Penjualan ]
```

Jangan menampilkan table kosong tanpa informasi.

---

# 44. Confirmation

Action destructive:

```text
Cancel
Delete
Adjustment
```

harus menggunakan confirmation dialog.

Contoh:

```text
Batalkan transaksi?

Transaksi INV-20260916-001
Rp157.000

Tindakan ini akan mengembalikan stock
yang sebelumnya dikurangi.

[ Batal ] [ Ya, Batalkan ]
```

---

# 45. Toast / Notification

Gunakan toast untuk success:

```text
✓ Transaksi berhasil disimpan.
```

Error:

```text
× Transaksi gagal disimpan.
```

Jangan menggunakan browser alert sebagai UI utama.

---

# 46. Responsive Design

Breakpoints:

```text
Mobile
Tablet
Desktop
```

Minimum target:

```text
360px mobile width
```

Sales form harus nyaman digunakan melalui smartphone.

---

# 47. Accessibility

Implementasikan minimal:

- semantic HTML
- label pada form
- keyboard accessible
- focus state
- sufficient contrast
- button memiliki label jelas
- jangan hanya menggunakan warna untuk membedakan status

Contoh:

```text
● PAID
✓ PAID
```

bukan hanya warna hijau.

---

# 48. Technical Architecture

Recommended:

```text
Laravel
├── Models
├── Controllers
├── Services
├── Requests
├── Policies
└── Jobs (future)

Inertia.js

Vue 3
├── Layouts
├── Components
├── Pages
└── Composables

SQLite
```

Business logic jangan diletakkan langsung di Vue.

---

# 49. Service Layer

Gunakan service untuk logic kompleks.

Minimal:

```text
InventoryService
FifoCostingService
SaleService
PurchaseService
ProfitService
DashboardService
```

Contoh:

```text
SaleService
    ↓
validate stock
    ↓
FifoCostingService
    ↓
InventoryService
    ↓
StockMovement
```

Controller tetap tipis.

---

# 50. Form Requests

Gunakan Laravel Form Request:

```text
StoreCustomerRequest
UpdateCustomerRequest

StoreSaleTypeRequest
UpdateSaleTypeRequest

StorePurchaseRequest

StoreSaleRequest
```

Validation harus berada di server.

---

# 51. Database Transactions

Gunakan:

```php
DB::transaction()
```

untuk:

### Purchase

```text
create purchase
+
create inventory layer
+
create stock movement
```

### Sale

```text
create sale
+
create sale items
+
consume FIFO layers
+
create stock movement
```

### Cancellation

```text
cancel transaction
+
restore inventory
+
create reversal movement
```

---

# 52. Invoice Number

Format:

```text
INV-YYYYMMDD-XXXX
```

Contoh:

```text
INV-20260916-0001
```

Purchase:

```text
PUR-20260916-0001
```

Invoice number harus unique.

---

# 53. Date and Currency

Application locale:

```text
id-ID
```

Currency:

```text
IDR / Rupiah
```

Display:

```text
Rp475.000
```

Database menyimpan integer:

```text
475000
```

Jangan menyimpan:

```text
"Rp475.000"
```

di database.

---

# 54. Decimal Handling

Untuk cost per egg:

```text
475000 / 180
= 2638.888888...
```

Database dapat menyimpan precision:

```text
DECIMAL(15,4)
```

atau equivalent SQLite handling.

Display:

```text
Rp2.639
```

Tetapi calculation menggunakan precision internal.

---

# 55. Testing

Wajib membuat automated tests untuk business-critical logic.

## Purchase Test

```text
Purchase 180 eggs
Rp475.000

Expected:
stock = 180
HPP = 2638.888...
```

## Sale Test

```text
Purchase 180
Sale 30

Expected:
stock = 150
```

## FIFO Test

```text
Purchase A
100 @ 2500

Purchase B
100 @ 2800

Sale
120

Expected COGS:
100 × 2500
+
20 × 2800
```

## Insufficient Stock

```text
Stock = 20
Sale = 30

Expected:
transaction rejected
stock remains 20
```

## Payment

```text
Invoice = 100000
Paid = 50000

Expected:
PARTIAL
Outstanding = 50000
```

## Full Payment

```text
Invoice = 100000
Paid = 100000

Expected:
PAID
Outstanding = 0
```

## Profit

Test:

```text
Revenue - COGS = Gross Profit
```

---

# 56. Seeder

Create demo data.

Seeder should include:

### User

```text
Demo User
demo@example.com
```

Use an obvious development-only password and clearly document it as demo credentials.

### Customers

```text
Budi
Sari
Andi
```

### Sale Types

```text
Pack
10
33000

Tray
30
91000

Ikat
180
510000
```

### Purchases

At least 2 batches with different HPP.

### Sales

Create several demo transactions demonstrating:

- paid
- unpaid
- partial
- shipped
- delivered
- FIFO consumption

---

# 57. README

GitHub README harus menjelaskan:

```text
Oh My Egg

Features

Tech Stack

Installation

Environment Setup

Database Setup

Seed Demo Data

Run Development Server

Testing

Project Structure

FIFO Explanation

Contribution

License
```

README harus mudah dipahami developer yang baru pertama kali melihat project.

---

# 58. Environment

`.env` jangan di-commit.

Provide:

```text
.env.example
```

Minimal:

```text
APP_NAME="Oh My Egg"
APP_ENV=local
APP_KEY=
APP_DEBUG=true

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Production:

```text
APP_DEBUG=false
```

---

# 59. Git Strategy

Recommended:

```text
main
develop
feature/*
```

Commit convention:

```text
feat:
fix:
refactor:
test:
docs:
style:
chore:
```

Example:

```text
feat: add FIFO inventory costing
fix: prevent sale above available stock
test: add FIFO costing scenarios
docs: update installation guide
```

---

# 60. Development Phases

AI Agent **WAJIB mengerjakan project secara bertahap**, bukan langsung membuat seluruh aplikasi sekaligus.

---

## STEP 0 — Project Initialization

Task:

- initialize Laravel
- configure SQLite
- install Inertia
- install Vue
- configure Vite
- configure frontend structure
- configure authentication
- configure ESLint/formatting jika diperlukan
- configure Git
- create README skeleton

Acceptance:

```text
Laravel runs
Vue runs
SQLite works
Login works
```

Jangan lanjut sebelum semua berhasil.

---

# STEP 1 — Design System

Buat:

```text
AppLayout
Sidebar
Topbar
Button
Input
Select
Modal
Toast
Badge
Card
Table
EmptyState
LoadingState
ConfirmDialog
```

Implementasikan Oh My Egg branding.

Pastikan semua komponen reusable.

Acceptance:

Semua komponen dapat digunakan konsisten.

---

# STEP 2 — Authentication

Implement:

- login
- logout
- protected route
- profile
- change password
- session security
- rate limiting

Testing:

```text
guest cannot access dashboard
valid user can login
invalid credential rejected
logout works
```

---

# STEP 3 — Database Foundation

Create migrations:

```text
users
customers
sale_types
purchases
inventory_layers
stock_movements
sales
sale_items
```

Pastikan:

- foreign key
- indexes
- unique constraints
- timestamps
- soft delete jika diperlukan

Acceptance:

```text
php artisan migrate
```

berhasil dari database kosong.

---

# STEP 4 — Master Data

Implement:

### Customer

CRUD.

### Sale Type

CRUD.

Rules:

- inactive item tidak tersedia untuk transaksi baru
- histori tetap aman

Testing CRUD.

---

# STEP 5 — Purchase

Implement:

```text
Purchase form
Purchase list
Purchase detail
```

Ketika purchase dibuat:

```text
Purchase
↓
Inventory Layer
↓
Stock Movement
```

Semua menggunakan DB transaction.

---

# STEP 6 — FIFO Engine

Ini merupakan core business logic.

Implement:

```text
FifoCostingService
```

Input:

```text
required_quantity
```

Output:

```text
layers consumed
total COGS
remaining layers
```

Buat automated tests sebanyak mungkin.

Jangan lanjut ke Sales sebelum FIFO test berhasil.

---

# STEP 7 — Stock

Implement:

- current stock
- inventory layers
- stock movement
- stock adjustment

Tambahkan consistency check.

---

# STEP 8 — Sales

Implement:

```text
Sales list
Create sale
Sale detail
```

Flow:

```text
Select Customer
↓
Select Sale Type
↓
Quantity
↓
Calculate eggs
↓
Check stock
↓
FIFO costing
↓
Calculate total
↓
Save
```

---

# STEP 9 — Payment & Delivery

Implement:

```text
UNPAID
PARTIAL
PAID
```

dan:

```text
PENDING
SHIPPED
DELIVERED
```

Tambahkan payment date dan delivered date.

---

# STEP 10 — Profit

Implement:

```text
Revenue
COGS
Gross Profit
Margin
```

Pastikan semua berasal dari transaksi aktual.

---

# STEP 11 — Dashboard

Setelah seluruh business logic selesai, baru dashboard dibuat.

Implement:

- KPI
- sales trend
- profit trend
- outstanding
- pending delivery
- low stock
- date filter

Dashboard hanya membaca data.

Jangan menaruh business mutation logic di dashboard.

---

# STEP 12 — Reports

Implement:

```text
Sales Report
Purchase Report
Profit Report
Stock Report
```

Tambahkan filtering.

Jika export CSV sederhana dapat dibuat tanpa mengganggu MVP, implementasikan.

---

# STEP 13 — Security Review

AI Agent harus melakukan security review terhadap:

- authentication
- authorization
- validation
- CSRF
- XSS
- SQL injection
- mass assignment
- session
- rate limiting
- secrets
- `.env`
- debug mode
- route exposure

---

# STEP 14 — UX Review

Review semua halaman.

Checklist:

```text
Consistent spacing
Consistent typography
Consistent button
Consistent form
Consistent status badge
Responsive mobile
Loading state
Empty state
Error state
Success notification
Confirmation
```

---

# STEP 15 — Testing

Run:

```text
php artisan test
```

Semua business-critical tests harus pass.

Minimum coverage:

```text
Authentication
Customer
Sale Type
Purchase
FIFO
Stock
Sales
Payment
Profit
Cancellation
```

---

# STEP 16 — Production Readiness

Check:

```text
APP_DEBUG=false

No secrets in Git

Database migration works

Seeder works

Storage permissions

Cache

Route cache

Config cache

Asset build
```

Run:

```text
npm run build
```

dan production Laravel optimization.

---

# STEP 17 — GitHub Release

Repository harus memiliki:

```text
README.md
LICENSE
.env.example
.gitignore
database migrations
seeders
tests
```

Jangan upload:

```text
.env
production database
node_modules
vendor
logs
credentials
```

---

# 61. Definition of Done

Project dianggap selesai jika:

### Authentication

- login bekerja
- logout bekerja
- protected routes bekerja

### Master

- customer CRUD bekerja
- sale type CRUD bekerja

### Purchase

- purchase dapat dibuat
- inventory layer dibuat
- stock bertambah

### FIFO

- FIFO calculation benar
- multiple layers bekerja
- partial layer consumption bekerja

### Sales

- multi item transaction bekerja
- stock validation bekerja
- FIFO COGS bekerja
- stock berkurang

### Payment

- unpaid
- partial
- paid
- outstanding calculation

### Delivery

- pending
- shipped
- delivered

### Profit

- revenue benar
- COGS benar
- profit benar
- margin benar

### Dashboard

- data akurat
- filter tanggal bekerja

### Security

- authentication
- authorization
- validation
- CSRF
- session security
- rate limiting

### UX

- responsive
- consistent
- mobile usable
- empty state
- error state
- loading state

### Testing

Semua critical business tests pass.

---

# 62. Important AI Coding Rules

AI Agent WAJIB mengikuti aturan berikut:

1. Jangan membuat seluruh aplikasi dalam satu langkah.
2. Ikuti STEP 0 sampai STEP 17 secara berurutan.
3. Setelah setiap step selesai, jalankan test/build yang relevan.
4. Jangan melanjutkan jika step sebelumnya gagal.
5. Jangan mengubah business rule tanpa persetujuan.
6. Jangan mengganti SQLite dengan database lain.
7. Jangan mengganti FIFO dengan weighted average.
8. Business logic harus berada di Laravel backend/service.
9. Vue hanya menangani presentation dan user interaction.
10. Gunakan reusable components.
11. Hindari duplicate code.
12. Gunakan database transaction untuk inventory operation.
13. Jangan menghapus histori transaksi secara destructive.
14. Jangan menyimpan currency sebagai formatted string.
15. Jangan menyimpan password plaintext.
16. Jangan commit `.env`.
17. Jangan expose sensitive error pada production.
18. Setiap perubahan database harus melalui migration.
19. Setiap business-critical feature harus memiliki automated test.
20. Jika terdapat ambiguity, berhenti dan jelaskan asumsi sebelum mengubah business rule.

---

# 63. Final Business Flow

## Purchase

```text
User
 ↓
Create Purchase
 ↓
Calculate HPP
 ↓
Create Inventory Layer
 ↓
Create Stock Movement
 ↓
Stock increases
```

## Sale

```text
User
 ↓
Select Customer
 ↓
Select Sale Type
 ↓
Input Quantity
 ↓
Calculate Egg Quantity
 ↓
Validate Stock
 ↓
FIFO Costing
 ↓
Create Sale
 ↓
Create Sale Items
 ↓
Consume Inventory Layers
 ↓
Create Stock Movement
 ↓
Calculate COGS
 ↓
Calculate Profit
```

## Payment

```text
UNPAID
   ↓
PARTIAL
   ↓
PAID
```

## Delivery

```text
PENDING
   ↓
SHIPPED
   ↓
DELIVERED
```

---

# 64. Future Roadmap

Future features, **jangan dibuat pada MVP**:

### Phase 2

- Supplier
- Expense
- multiple payments
- receipt/invoice printing
- CSV export
- WhatsApp notification
- low-stock notification

### Phase 3

- multi-user
- roles & permissions
- audit log
- cloud deployment
- backup/restore
- PWA/mobile experience

### Phase 4

- customer portal
- online ordering
- payment gateway
- analytics
- forecasting
- multiple branches

---

# 65. Product Success Criteria

Oh My Egg berhasil jika pemilik usaha dapat melakukan proses berikut tanpa membutuhkan bantuan teknis:

```text
1. Login

2. Catat pembelian telur
   1 ikat
   180 telur
   Rp475.000

3. System otomatis mengetahui:
   Stock = 180
   HPP = Rp2.638,89

4. Catat penjualan
   2 Pack

5. System otomatis:
   20 telur keluar
   FIFO cost dihitung
   revenue dihitung
   profit dihitung

6. User tandai:
   Paid
   Delivered

7. Dashboard menunjukkan:
   Sales
   Cash Received
   Outstanding
   COGS
   Profit
   Stock
```

Jika seluruh flow tersebut berjalan dengan sederhana dan akurat, maka MVP **Oh My Egg** dianggap berhasil.

---

# ADDENDUM A — Perubahan & Keputusan Implementasi

> Addendum ini melengkapi PRD tanpa mengubah isi asli di atas. Bila terjadi pertentangan, addendum ini yang berlaku.

## A.1 Versi & Stack

- Laravel 12 (PHP 8.2) dengan starter kit resmi `laravel/vue-starter-kit` tag `v1.0.2`
  (Inertia 2, Vue 3 Composition API + TypeScript, Tailwind CSS, komponen shadcn-vue).
- Automated test memakai **Pest 3**.
- **Registrasi publik dan email verification dihapus** (konsep single-owner). User dibuat melalui seeder.
- Konfigurasi: `APP_NAME="Oh My Egg"`, `APP_LOCALE=id`, `APP_TIMEZONE=Asia/Jakarta`.
- Tema **terang saja**; dark mode dan toggle appearance dihapus.
- UI **mobile-first**: tabel berubah menjadi daftar kartu pada layar kecil, form satu kolom, sidebar menjadi menu off-canvas.

## A.2 Tabel Tambahan & Perubahan Field

1. **`sale_item_consumptions`** (tabel baru)

   ```text
   id
   sale_item_id      (FK sale_items)
   inventory_layer_id (FK inventory_layers)
   quantity
   unit_cost          DECIMAL(15,4)
   total_cost         DECIMAL(15,4)
   created_at, updated_at
   ```

   Berfungsi mencatat layer FIFO mana yang dikonsumsi oleh tiap sale item, sehingga
   pembatalan transaksi dapat mengembalikan layer secara tepat.

2. **`sales`** — tambah `status` (`ACTIVE`/`CANCELLED`), `cancelled_at`, `cancel_reason`,
   `created_by`, `updated_by`.

3. **`purchases`** — tambah `status` (`ACTIVE`/`CANCELLED`), `cancelled_at`, `cancel_reason`,
   `created_by`, `updated_by`.

4. **`stock_movements`** — tambah `created_by`; `movement_type` bertambah
   `SALE_CANCEL` dan `PURCHASE_CANCEL` untuk reversal.

5. **`settings`** (tabel baru)

   ```text
   id
   key    (unique)
   value  (text, nullable)
   created_at, updated_at
   ```

   Dipakai minimal untuk `minimum_stock`.

## A.3 Tipe Data Uang

- Nilai harga/amount disimpan sebagai **integer rupiah** (contoh: `475000`), tidak pernah string berformat.
- Nilai biaya yang butuh presisi (`cost_per_egg`, `unit_cost`, `total_cost`, `profit`) memakai `DECIMAL(15,4)`.
- Perhitungan memakai presisi internal; tampilan dibulatkan ke rupiah penuh (contoh: `Rp2.639`).

## A.4 Pembatalan (Void) & Reversal

- **Sale batal**: kembalikan `quantity_remaining` pada setiap layer yang tercatat di
  `sale_item_consumptions`, buat stock movement `SALE_CANCEL` (quantity positif),
  lalu set `status = CANCELLED`. Seluruh proses dalam `DB::transaction`.
- **Purchase batal**: hanya diizinkan jika layer belum dikonsumsi
  (`quantity_remaining == quantity_received`). Buat movement `PURCHASE_CANCEL` dan nonaktifkan layer.
- Transaksi tersimpan **tidak boleh** mengubah item/quantity. Koreksi dilakukan dengan
  membatalkan transaksi lalu membuat transaksi baru.

## A.5 Stock Adjustment

Diimplementasikan sebagai `stock_movements` bertipe `ADJUSTMENT_IN` / `ADJUSTMENT_OUT` dengan
`reason` wajib (disimpan pada `notes`, `reference_type = ADJUSTMENT`). Tidak ada tabel terpisah.

Agar stok tetap konsisten dengan inventory layer:

- `ADJUSTMENT_IN` membuat **inventory layer baru** (tanpa `purchase_id`), dengan `unit_cost`
  mengikuti HPP layer terakhir yang tersedia.
- `ADJUSTMENT_OUT` mengurangi stok lewat mekanisme **FIFO consume**, sehingga tidak boleh
  melebihi stok yang tersedia (stok tidak boleh negatif).

## A.6 Konsistensi Stock

Validasi internal membandingkan:

```text
SUM(stock_movements.quantity) == SUM(inventory_layers.quantity_remaining)
```

Jika tidak sama, tampilkan peringatan pada halaman Stock / laporan.

## A.7 Nomor Invoice

- Penjualan: `INV-YYYYMMDD-####`
- Pembelian: `PUR-YYYYMMDD-####`

Urut per hari, unik (unique index), dibuat di dalam database transaction.

## A.8 Status & Aksesibilitas

- Status ditampilkan dengan **ikon + teks**, bukan hanya warna
  (contoh: `✓ Lunas`, `⚠ Sebagian`, `✕ Dibatalkan`).
- Destructive action memakai confirmation dialog.
- Feedback sukses/gagal memakai toast, bukan browser alert.