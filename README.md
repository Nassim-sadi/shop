# Laravel Vue Shop with Admin Panel

A modern, full-stack eCommerce application built with **Laravel** and **Vue 3**, designed for performance, maintainability, and scalability. It includes a robust admin panel for managing products, categories, variants, and users.

---

![Vue](https://img.shields.io/badge/Vue-3.x-42b883?logo=vue.js)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red?logo=laravel)
![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)

## ✨ Features

### 🛒 Shop

- Product listing with pagination
- Product detail page with variant management
- Category-based navigation
- Optimized image loading (lazy loading, blurred fallback background)
- Mobile-responsive design

### 🧑‍💼 Admin Panel

- Fully-featured user, role, and permission management
- Category, product, and variant CRUD
- Status toggles, soft deletes, restoration, and permanent deletion
- Clean and intuitive layout

### ⚙️ Tech Stack

- **Laravel** – REST API backend
- **Vue 3** + **Composition API**
- **Pinia** – State management
- **PrimeVue 4** – UI components
- **Tailwind CSS** – Custom styling
- **Axios** – API communication
- **Jenssegers Agent** – Track browser & OS usage
- **Vite** – Lightning-fast development

---

## ⚡ UX Enhancements

- Lazy-loaded images to reduce bandwidth
- Blur background trick for inconsistent image ratios
- Real-time UI feedback (loading indicators, toast notifications)
- Route-based code splitting and lazy-loading of views

---

## 🧩 Setup & Installation

```bash
git clone https://github.com/Nassim-sadi/shop.git
cd laravel-vue-shop
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## 🔧 Configuration

- Set your database credentials in `.env`
- Configure your mailing service (e.g., Mailgun, SMTP)
- Set the `APP_NAME` and `APP_URL` in `.env`

---

## 🚀 Usage

```bash
npm run dev         # Start frontend dev server
php artisan serve   # Start Laravel backend
php artisan queue:listen   # For handling jobs like emails or uploads
```

---

### 🚧 Missing Features

The following features are planned but not yet implemented:

- [ ] 🛒 **Shopping Cart** – Allow customers to add, remove, and update product quantities before checkout.
- [ ] 🔍 **Product Search (Client)** – Enable keyword-based search for products on the customer side.
- [ ] 📦 **Order Management (Client/Admin)** – Clients can place and view orders, admins can manage and fulfill them.
- [x] 🛎️ **Admin Product Search** – Already implemented. The client-side search will share similar logic for consistency and code reuse.

---

## 📝 License

MIT — Free to use, modify, and distribute.

---

## 🙌 Credits

Crafted with ❤️ by **Nassim Sadi**  
Powered by [Laravel](https://laravel.com), [Vue 3](https://vuejs.org), [PrimeVue](https://primevue.org), and [Tailwind CSS](https://tailwindcss.com)
