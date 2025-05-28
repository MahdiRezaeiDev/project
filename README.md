# 📞 Small Business MIS (Call Centre Management System)

This repository contains the source code for a comprehensive **Management Information System (MIS)** designed to support all essential operations of a small business that involves  **advertising, customer care (call centre), purchasing, selling** , and  **inventory management** .

---

## ✨ Features

* 📇 **Customer Management**

  Track, update, and manage customer records and communications.
* 🧾 **Billing System**

  Generate, issue, and track customer bills with payment history.
* 🛆 **Inventory Management**

  Add, update, and manage stock items with full purchase/sell history.
* 🛍️ **Sales & Purchases**

  Complete system to handle buying from vendors and selling to customers.
* 📞 **Call Centre Dashboard**

  Interface for handling incoming/outgoing calls, with notes and call logs.
* 📊 **Reports & Analytics**

  Sales reports, inventory summaries, and customer billing insights.

---

## 🛠️ Tech Stack

* **Backend:**  PHP
* **Frontend:**  TailwindCSS / javascript
* **Database:** MySQL
* **Optional:** jobs, Queue Workers, API Integration

---

## 🚀 Getting Started

### Requirements

* PHP >= 8.1
* Composer
* MySQL or compatible DB
* Node.js & npm (for assets)

### Installation

1. Clone the repository:

```bash
git clone https://github.com/MahdiRezaeiDev/yadak-shop.git
cd yadak-shop
```

2. Install dependencies:

```bash
composer install
npm install && npm run dev
```

3. Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

4. Run database migrations and seed (optional):

```bash
php artisan migrate --seed
```

5. Start the server:

```bash
php artisan serve
```

## 🧹 Future Improvements

* SMS or WhatsApp Notifications
* PDF Bill Exporting
* Advanced Inventory Forecasting
* AI-based customer query suggestions

---

## 🧑‍💻 Contribution

Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

---

## 📄 License

This project is open-sourced under the [MIT License](https://chatgpt.com/c/LICENSE).

---

## 📬 Contact

Created with ❤️ by [Mahdi Rezaei]

For business inquiries: mahdi.sohaily4030@gmail.com
