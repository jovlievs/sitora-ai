<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $job common\models\TranscriptionJob */

$this->title = 'Natija - Ovoza';

// Check if guest has access to this job
$session = Yii::$app->session;
$guestJobs = $session->get('guest_jobs', []);

if (!in_array($job->id, $guestJobs)) {
    // Redirect if trying to access someone else's guest job
    return $this->redirect(['site/index']);
}

$remainingTrials = 3 - count($guestJobs);
?>

<style>
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
        min-height: 100vh;
        padding: 2rem;
    }

    .result-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        color: var(--text-light);
        font-size: 1.75rem;
        font-weight: 700;
    }

    .logo-icon {
        width: 48px;
        height: 48px;
        background: var(--primary-purple);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .trial-badge {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .result-card {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(124, 58, 237, 0.2);
    }

    .status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .status-processing {
        background: rgba(59, 130, 246, 0.15);
        color: #60A5FA;
    }

    .status-completed {
        background: rgba(16, 185, 129, 0.15);
        color: #34D399;
    }

    .status-failed {
        background: rgba(239, 68, 68, 0.15);
        color: #F87171;
    }

    .transcription-text {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(124, 58, 237, 0.2);
        border-radius: 12px;
        padding: 1.5rem;
        line-height: 1.8;
        font-size: 1.125rem;
        margin: 1.5rem 0;
        min-height: 150px;
    }

    .loading-animation {
        text-align: center;
        padding: 3rem;
    }

    .spinner {
        width: 48px;
        height: 48px;
        border: 4px solid rgba(124, 58, 237, 0.2);
        border-top-color: var(--primary-purple);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .cta-section {
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(167, 139, 250, 0.1));
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        border: 1px solid rgba(124, 58, 237, 0.3);
    }

    .cta-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }

    .cta-subtitle {
        color: var(--text-muted);
        margin-bottom: 1.5rem;
    }

    .btn-primary {
        display: inline-block;
        padding: 1rem 2rem;
        background: var(--primary-purple);
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background: var(--light-purple);
        transform: translateY(-2px);
    }

    .btn-secondary {
        display: inline-block;
        padding: 1rem 2rem;
        background: rgba(124, 58, 237, 0.15);
        color: var(--primary-purple);
        text-decoration: none;
        border-radius: 10px;
        font-weight: 600;
        margin-left: 1rem;
    }
</style>

<div class="result-container">
    <div class="header">
        <a href="<?= Url::home() ?>" class="logo">
            <div class="logo-icon">🎤</div>
            <span>Ovoza</span>
        </a>

        <?php if ($remainingTrials > 0): ?>
            <div class="trial-badge">
                🎁 <?= $remainingTrials ?> ta bepul sinov qoldi
            </div>
        <?php endif; ?>
    </div>

    <div class="result-card">
        <h2 style="margin-bottom: 1rem;">Transkribatsiya natijasi</h2>

        <div>
            <strong>Fayl nomi:</strong> <?= Html::encode($job->audio_filename) ?>
        </div>
        <div style="margin-top: 0.5rem;">
            <strong>Davomiyligi:</strong> <?= $job->getFormattedDuration() ?>
        </div>
        <div style="margin-top: 0.5rem;">
            <strong>Holat:</strong>
            <span class="status-badge status-<?= $job->status ?>">
                <?= $job->getStatusLabel() ?>
            </span>
        </div>

        <?php if ($job->status === 'completed'): ?>
            <h3 style="margin-top: 2rem; margin-bottom: 1rem;">📝 Matn:</h3>
            <div class="transcription-text">
                <?= Html::encode($job->transcription_text) ?>
            </div>

            <button onclick="copyToClipboard()" style="padding: 0.75rem 1.5rem; background: var(--primary-purple); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                📋 Nusxa olish
            </button>

        <?php elseif ($job->status === 'processing' || $job->status === 'pending'): ?>
            <div class="loading-animation">
                <div class="spinner"></div>
                <p>Transkribatsiya qilinmoqda...</p>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.5rem;">
                    Bu sahifa avtomatik yangilanadi
                </p>
            </div>

        <?php elseif ($job->status === 'failed'): ?>
            <div style="text-align: center; padding: 2rem; color: #F87171;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">❌</div>
                <p>Transkribatsiya muvaffaqiyatsiz tugadi</p>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">Iltimos qayta urinib ko'ring</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($remainingTrials == 0): ?>
        <div class="cta-section">
            <h2 class="cta-title">🎉 Bepul sinovlar tugadi!</h2>
            <p class="cta-subtitle">
                Ro'yxatdan o'ting va 10,000 UZS bonus oling
            </p>
            <a href="<?= Url::to(['site/signup']) ?>" class="btn-primary">
                Ro'yxatdan o'tish →
            </a>
            <a href="<?= Url::home() ?>" class="btn-secondary">
                Asosiy sahifa
            </a>
        </div>
    <?php else: ?>
        <div class="cta-section">
            <h2 class="cta-title">Yana sinab ko'ring!</h2>
            <p class="cta-subtitle">
                Sizda yana <?= $remainingTrials ?> ta bepul sinov bor
            </p>
            <a href="<?= Url::home() ?>" class="btn-primary">
                Yana yuklash →
            </a>
            <a href="<?= Url::to(['site/signup']) ?>" class="btn-secondary">
                Ro'yxatdan o'tish
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
    // Auto-refresh if processing
    // Auto-refresh and check status
    <?php if ($job->status === 'processing' || $job->status === 'pending'): ?>
    setInterval(function() {
        // Call status endpoint to trigger Aisha check
        fetch('<?= Url::to(['transcription/status', 'id' => $job->id]) ?>')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'completed' || data.status === 'failed') {
                    location.reload();
                }
            });
    }, 5000);
    <?php endif; ?>

    function copyToClipboard() {
        const text = document.querySelector('.transcription-text').textContent;
        navigator.clipboard.writeText(text).then(() => {
            alert('✅ Matn nusxa olindi!');
        }).catch(() => {
            alert('❌ Nusxa olishda xatolik');
        });
    }
</script>