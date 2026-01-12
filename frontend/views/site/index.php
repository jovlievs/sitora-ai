<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Ovoza - ovozni matnga aylantirish';
?>

<style>
    /* Dark Purple Theme for Ovoza */
    :root {
        --dark-bg: #0F0F1E;
        --card-bg: #1A1A2E;
        --primary-purple: #7C3AED;
        --light-purple: #A78BFA;
        --text-light: #E2E8F0;
        --text-muted: #94A3B8;
        --success: #10B981;
    }

    body {
        background: var(--dark-bg);
        color: var(--text-light);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        overflow-x: hidden;
    }

    /* Animated Background */
    .hero-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: linear-gradient(135deg, #0F0F1E 0%, #1A1A2E 50%, #2D1B69 100%);
        z-index: -1;
    }

    .hero-bg::before {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(124, 58, 237, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        top: -200px;
        right: -200px;
        animation: pulse 8s ease-in-out infinite;
    }

    .hero-bg::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(167, 139, 250, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        bottom: -150px;
        left: -150px;
        animation: pulse 6s ease-in-out infinite reverse;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.2); opacity: 0.8; }
    }

    /* Header */
    .ovoza-header {
        padding: 1.5rem 0;
        background: rgba(26, 26, 46, 0.8);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(124, 58, 237, 0.2);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .ovoza-logo {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-light);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .ovoza-logo-icon {
        width: 36px;
        height: 36px;
        background: var(--primary-purple);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .header-nav {
        display: flex;
        gap: 2rem;
        align-items: center;
    }

    .header-nav a {
        color: var(--text-muted);
        text-decoration: none;
        transition: color 0.3s;
    }

    .header-nav a:hover {
        color: var(--text-light);
    }

    .lang-switcher {
        display: flex;
        gap: 0.5rem;
        padding: 0.5rem;
        background: var(--card-bg);
        border-radius: 8px;
    }

    .lang-btn {
        padding: 0.25rem 0.75rem;
        border: none;
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.3s;
    }

    .lang-btn.active {
        background: var(--primary-purple);
        color: white;
    }

    .btn-login {
        padding: 0.625rem 1.5rem;
        background: var(--primary-purple);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-login:hover {
        background: var(--light-purple);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(124, 58, 237, 0.3);
    }

    /* Hero Section */
    .hero-section {
        min-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem 0;
    }

    .hero-content {
        text-align: center;
        max-width: 900px;
        margin: 0 auto;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, #E2E8F0 0%, #A78BFA 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.2;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        color: var(--text-muted);
        margin-bottom: 3rem;
    }

    /* Upload Card */
    .upload-card {
        background: var(--card-bg);
        border: 2px dashed rgba(124, 58, 237, 0.3);
        border-radius: 16px;
        padding: 3rem;
        margin: 2rem auto;
        max-width: 700px;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .upload-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--primary-purple), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .upload-card:hover {
        border-color: var(--primary-purple);
        box-shadow: 0 20px 60px rgba(124, 58, 237, 0.2);
        transform: translateY(-4px);
    }

    .upload-icon {
        font-size: 4rem;
        color: var(--primary-purple);
        margin-bottom: 1.5rem;
    }

    .upload-text {
        font-size: 1.125rem;
        color: var(--text-light);
        margin-bottom: 2rem;
    }

    .upload-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-primary, .btn-secondary {
        padding: 1rem 2rem;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: var(--primary-purple);
        color: white;
    }

    .btn-primary:hover {
        background: var(--light-purple);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(124, 58, 237, 0.4);
    }

    .btn-secondary {
        background: var(--card-bg);
        color: var(--text-light);
        border: 1px solid rgba(124, 58, 237, 0.3);
    }

    .btn-secondary:hover {
        border-color: var(--primary-purple);
        background: rgba(124, 58, 237, 0.1);
    }

    /* Features Section */
    .features-section {
        padding: 6rem 0;
        background: rgba(26, 26, 46, 0.3);
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }

    .feature-card {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: 12px;
        text-align: center;
        transition: all 0.3s;
        border: 1px solid transparent;
    }

    .feature-card:hover {
        transform: translateY(-8px);
        border-color: var(--primary-purple);
        box-shadow: 0 15px 40px rgba(124, 58, 237, 0.2);
    }

    .feature-icon {
        width: 60px;
        height: 60px;
        background: rgba(124, 58, 237, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin: 0 auto 1.5rem;
        color: var(--primary-purple);
    }

    .feature-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: var(--text-light);
    }

    .feature-description {
        color: var(--text-muted);
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .upload-card {
            padding: 2rem 1.5rem;
        }

        .header-nav {
            gap: 1rem;
        }
    }

    /* Hidden file input */
    #audio-file {
        display: none;
    }
</style>

<div class="hero-bg"></div>

<!-- Header -->
<header class="ovoza-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3">
                <a href="<?= Url::home() ?>" class="ovoza-logo">
                    <div class="ovoza-logo-icon">🎤</div>
                    <span>Ovoza</span>
                </a>
            </div>
            <div class="col-md-6">
                <nav class="header-nav justify-content-center">
                    <a href="#features">Imkoniyatlar</a>
                    <a href="#about">Biz haqimizda</a>
                    <a href="#contact">Aloqa</a>
                </nav>
            </div>
            <div class="col-md-3 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <div class="lang-switcher">
                        <button class="lang-btn active">UZ</button>
                        <button class="lang-btn">RU</button>
                        <button class="lang-btn">EN</button>
                    </div>
                    <?php if (Yii::$app->user->isGuest): ?>
                        <a href="<?= Url::to(['site/login']) ?>" class="btn-login">Kirish</a>
                    <?php else: ?>
                        <a href="<?= Url::to(['transcription/index']) ?>" class="btn-login">Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Ovozni matnga aylantiring</h1>
            <p class="hero-subtitle">O'zbek tili uchun professional nutqni tanish texnologiyasi</p>

            <div class="upload-card">
                <div class="upload-icon">🎙️</div>
                <p class="upload-text">Audio faylni yuklang yoki ovozingizni yozib oling</p>

                <div class="upload-actions">
                    <button class="btn-primary" onclick="document.getElementById('audio-file').click()">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                        </svg>
                        Fayl yuklash
                    </button>

                    <button class="btn-secondary" onclick="alert('Ovoz yozish funksiyasi tez orada qo\'shiladi!')">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M3.5 6.5A.5.5 0 0 1 4 7v1a4 4 0 0 0 8 0V7a.5.5 0 0 1 1 0v1a5 5 0 0 1-4.5 4.975V15h3a.5.5 0 0 1 0 1h-7a.5.5 0 0 1 0-1h3v-2.025A5 5 0 0 1 3 8V7a.5.5 0 0 1 .5-.5z"/>
                            <path d="M10 8a2 2 0 1 1-4 0V3a2 2 0 1 1 4 0v5zM8 0a3 3 0 0 0-3 3v5a3 3 0 0 0 6 0V3a3 3 0 0 0-3-3z"/>
                        </svg>
                        Ovoz yozish
                    </button>
                </div>

                <input type="file" id="audio-file" accept=".wav,.mp3,.ogg" onchange="handleFileSelect(this)">

                <p style="margin-top: 1.5rem; color: var(--text-muted); font-size: 0.875rem;">
                    Qo'llab-quvvatlanadigan formatlar: WAV, MP3, OGG (maksimal 100MB)
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section" id="features">
    <div class="container">
        <div class="text-center">
            <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">Nima uchun Ovoza?</h2>
            <p style="color: var(--text-muted); font-size: 1.125rem;">O'zbek tili uchun eng yaxshi nutqni tanish platformasi</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3 class="feature-title">Tez ishlaydi</h3>
                <p class="feature-description">1 daqiqalik audio 10 soniyada matnga aylanadi</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3 class="feature-title">Yuqori aniqlik</h3>
                <p class="feature-description">95%+ aniqlik bilan o'zbek tilini taniydi</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Xavfsiz</h3>
                <p class="feature-description">Ma'lumotlaringiz shifrlangan holda saqlanadi</p>
            </div>
        </div>
    </div>
</section>

<script>
    // Track guest uploads in session
    function getGuestUploadCount() {
        return parseInt(sessionStorage.getItem('guest_uploads') || '0');
    }

    function incrementGuestUploadCount() {
        const count = getGuestUploadCount() + 1;
        sessionStorage.setItem('guest_uploads', count);
        return count;
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            var file = input.files[0];

            <?php if (Yii::$app->user->isGuest): ?>
            // Guest user - check free trial limit
            const uploadCount = getGuestUploadCount();

            if (uploadCount >= 3) {
                alert('🎁 3 ta bepul sinov yakunlandi!\n\nRo\'yxatdan o\'ting va 10,000 UZS bonus oling!');
                window.location.href = '<?= Url::to(['site/signup']) ?>';
                return;
            }

            // Upload as guest
            uploadGuestAudio(file);
            <?php else: ?>
            // Logged in user - redirect to dashboard
            window.location.href = '<?= Url::to(['transcription/index']) ?>';
            <?php endif; ?>
        }
    }

    function uploadGuestAudio(file) {
        // Show upload UI
        const uploadCard = document.querySelector('.upload-card');
        uploadCard.innerHTML = `
        <div class="upload-icon">⏳</div>
        <p class="upload-text">Audio yuklanmoqda...</p>
        <div style="background: rgba(124, 58, 237, 0.2); height: 8px; border-radius: 4px; overflow: hidden; margin: 1rem 0;">
            <div style="background: var(--primary-purple); height: 100%; width: 0%; transition: width 0.3s;" id="progress-bar"></div>
        </div>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Iltimos kuting...</p>
    `;

        const formData = new FormData();
        formData.append('audio', file);
        formData.append('guest', '1');

        // Get CSRF token from cookie or generate request
        const csrfToken = getCsrfToken();

        // Start progress animation
        let progress = 0;
        const progressBar = document.getElementById('progress-bar');
        const progressInterval = setInterval(() => {
            progress += 5;
            if (progress <= 90) {
                progressBar.style.width = progress + '%';
            }
        }, 200);

        // Use jQuery AJAX for proper CSRF handling
        $.ajax({
            url: '<?= Url::to(['transcription/upload']) ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';

                if (data.success) {
                    incrementGuestUploadCount();
                    const remaining = 3 - getGuestUploadCount();

                    // Show success and redirect to guest result page
                    uploadCard.innerHTML = `
                    <div class="upload-icon">✅</div>
                    <p class="upload-text" style="color: var(--success);">Muvaffaqiyatli yuklandi!</p>
                    <p style="color: var(--text-muted); margin-top: 1rem;">
                        ${remaining > 0 ? `Sizda yana ${remaining} ta bepul sinov bor` : 'Barcha bepul sinovlar yakunlandi'}
                    </p>
                `;

                    setTimeout(() => {
                        window.location.href = '<?= Url::to(['site/guest-result']) ?>?job=' + data.jobId;
                    }, 1500);
                } else {
                    uploadCard.innerHTML = `
                    <div class="upload-icon">❌</div>
                    <p class="upload-text" style="color: var(--danger);">Xatolik yuz berdi</p>
                    <p style="color: var(--text-muted); margin-top: 1rem;">${data.message || 'Noma\'lum xato'}</p>
                    <button onclick="location.reload()" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: var(--primary-purple); color: white; border: none; border-radius: 8px; cursor: pointer;">
                        Qayta urinish
                    </button>
                `;
                }
            },
            error: function(xhr, status, error) {
                clearInterval(progressInterval);
                console.error('Upload error:', error);
                console.error('Response:', xhr.responseText);

                uploadCard.innerHTML = `
                <div class="upload-icon">❌</div>
                <p class="upload-text" style="color: var(--danger);">Xatolik yuz berdi</p>
                <p style="color: var(--text-muted); margin-top: 1rem;">Server bilan bog\'lanishda xatolik</p>
                <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.5rem;">${error}</p>
                <button onclick="location.reload()" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: var(--primary-purple); color: white; border: none; border-radius: 8px; cursor: pointer;">
                    Qayta urinish
                </button>
            `;
            }
        });
    }

    function getCsrfToken() {
        // Try to get CSRF token from meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }

        // Try to get from cookie
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === '_csrf' || name === 'CSRF-TOKEN') {
                return decodeURIComponent(value);
            }
        }

        return '';
    }
</script>