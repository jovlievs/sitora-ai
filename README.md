# 🎤 Ovoza - Uzbek Speech-to-Text Platform

> AI-powered speech-to-text transcription service for the Uzbek language

[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php)](https://www.php.net/)
[![Yii2](https://img.shields.io/badge/Yii2-2.0-green?style=flat)](https://www.yiiframework.com/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

---

## 📖 About

**Ovoza** is a web platform that converts Uzbek speech into text using custom-trained AI models. Built to make transcription accessible and easy for everyone.

### Key Features

- 🇺🇿 **Uzbek Language Specialized** - Custom STT model trained for Uzbek
- 🎁 **Free Trial** - 3 free transcriptions (2 min each) without signup
- ⚡ **Real-time Processing** - Instant transcription with live status updates
- 🔐 **Secure & Private** - Your audio files are protected
- 📱 **Responsive Design** - Works on desktop and mobile

---

## ✨ What You Can Do

### For Everyone (No Login Required)
- Upload audio and get transcription instantly
- Try 3 times for free (up to 2 minutes each)
- Copy results to clipboard
- Support: WAV, MP3, OGG formats

### For Registered Users
- Access all your transcriptions
- Track processing history
- Manage multiple jobs
- Longer audio support (up to 30 minutes)

---

## 🚀 Tech Stack

**Backend**
- PHP (Yii2 Framework)
- MySQL Database
- Job Queue System

**Frontend**
- HTML5/CSS3, Bootstrap 5
- JavaScript (jQuery, AJAX)
- Dark theme UI

**AI/ML**
- Custom Uzbek STT model
- Python-based processing engine
- GPU-accelerated transcription

---

## 🗺️ Project Status

### ✅ Live Features
- Guest trial system
- User authentication
- Real-time job tracking
- Multi-format audio support
- Responsive interface

### 🚧 In Development
- Authenticated user uploads
- Model accuracy improvements
- Browser audio recorder

### 📅 Coming Soon
- Multi-language interface (UZ/RU/EN)
- Batch upload
- Mobile apps
- Developer API

---
---

## 📂 Project Structure
```
ovoza/
├── common/
│   ├── config/              # Shared configuration files
│   │   ├── main.php
│   │   ├── main-local.php   # Local config (gitignored)
│   │   └── params.php
│   └── models/              # Database models
│       ├── User.php         # User authentication & wallet
│       ├── TranscriptionJob.php  # Job records
│       ├── Task.php         # Processing tasks
│       ├── Transaction.php  # Wallet transactions
│       └── SystemSetting.php # App settings
│
├── frontend/
│   ├── controllers/
│   │   ├── SiteController.php         # Landing, login, signup
│   │   └── TranscriptionController.php # Upload, dashboard, jobs
│   ├── models/              # Frontend-specific models
│   ├── views/
│   │   ├── layouts/         # Page layouts
│   │   │   ├── main.php     # Authenticated user layout
│   │   │   ├── clean.php    # Guest/minimal layout
│   │   │   └── dashboard.php # Dashboard layout
│   │   ├── site/
│   │   │   ├── index.php    # Landing page
│   │   │   ├── login.php
│   │   │   ├── signup.php
│   │   │   └── guest-result.php  # Guest transcription result
│   │   └── transcription/
│   │       ├── index.php    # Dashboard (job list)
│   │       └── view.php     # Job details
│   ├── web/
│   │   ├── css/             # Stylesheets
│   │   ├── js/              # JavaScript files
│   │   ├── uploads/         # User audio files
│   │   │   └── 2026/        # Organized by date
│   │   └── index.php        # Entry point
│   └── config/
│       ├── main.php
│       └── main-local.php   # Local config (gitignored)
│
├── backend/                 # Admin panel (future development)
│
├── console/                 # CLI commands
│   └── controllers/         # Background tasks
│
├── environments/            # Environment templates
│   ├── dev/                 # Development config templates
│   └── prod/                # Production config templates
│
├── vendor/                  # Composer dependencies (gitignored)
│
└── demos/                   # Demo videos
    └── demo.mp4
```

## 🎯 Mission

Building high-quality STT/TTS technology for the Uzbek language to support content creators, journalists, students, and businesses.

---

## 📞 Connect With Me

**Abbos Jovliev** - Co-Founder & Developer

📧 [jovliyevabbosjon@gmail.com](mailto:jovliyevabbosjon@gmail.com)  
💼 [LinkedIn](https://www.linkedin.com/in/abbos-jovliev/)

---

## 📄 License

MIT License - feel free to learn from this project!

---

## 🎥 Demo

**See Ovoza in action:**

https://github.com/user-attachments/assets/37fc3cd1-3c17-4e00-8089-a03830c18a67

Watch a guest user upload audio and receive instant transcription without any signup!

---

**Built with ❤️ for the Uzbek language** 🇺🇿