<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $jobs common\models\TranscriptionJob[] */

$this->title = 'Dashboard - Ovoza';

// Get current user
$user = Yii::$app->user->identity;
?>

    <style>
        /* Dashboard Dark Theme */
        :root {
            --dark-bg: #0F0F1E;
            --sidebar-bg: #1A1A2E;
            --card-bg: #252542;
            --primary-purple: #7C3AED;
            --light-purple: #A78BFA;
            --text-light: #E2E8F0;
            --text-muted: #94A3B8;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
        }

        body {
            background: var(--dark-bg);
            color: var(--text-light);
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .dashboard-sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid rgba(124, 58, 237, 0.1);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }

        .sidebar-logo {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(124, 58, 237, 0.1);
        }

        .sidebar-logo-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-light);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .sidebar-logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-purple);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1.5rem 1rem;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.75rem;
            padding: 0 0.75rem;
            letter-spacing: 0.05em;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }

        .nav-item:hover {
            background: rgba(124, 58, 237, 0.1);
            color: var(--text-light);
        }

        .nav-item.active {
            background: rgba(124, 58, 237, 0.15);
            color: var(--text-light);
            font-weight: 600;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Wallet Card */
        .sidebar-wallet {
            padding: 1rem;
            border-top: 1px solid rgba(124, 58, 237, 0.1);
        }

        .wallet-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.25rem;
        }

        .wallet-label {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .wallet-amount {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .wallet-currency {
            font-size: 1rem;
            color: var(--text-muted);
        }

        /* Main Content */
        .dashboard-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .greeting {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .greeting-subtitle {
            color: var(--text-muted);
        }

        /* Quick Actions Grid */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .action-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary-purple);
            box-shadow: 0 20px 40px rgba(124, 58, 237, 0.2);
        }

        .action-icon {
            width: 80px;
            height: 80px;
            background: rgba(124, 58, 237, 0.15);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }

        .action-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .action-description {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* Jobs Section */
        .jobs-section {
            background: var(--sidebar-bg);
            border-radius: 16px;
            padding: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .jobs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .jobs-table thead th {
            text-align: left;
            padding: 1rem;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.875rem;
            border-bottom: 1px solid rgba(124, 58, 237, 0.1);
        }

        .jobs-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid rgba(124, 58, 237, 0.05);
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending { background: rgba(251, 191, 36, 0.15); color: #FCD34D; }
        .status-processing { background: rgba(59, 130, 246, 0.15); color: #60A5FA; }
        .status-completed { background: rgba(16, 185, 129, 0.15); color: #34D399; }
        .status-failed { background: rgba(239, 68, 68, 0.15); color: #F87171; }
        .status-cancelled { background: rgba(148, 163, 184, 0.15); color: #94A3B8; }

        .btn-action {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-view {
            background: rgba(124, 58, 237, 0.15);
            color: var(--primary-purple);
        }

        .btn-view:hover {
            background: rgba(124, 58, 237, 0.25);
        }

        .btn-cancel {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }

        .btn-cancel:hover {
            background: rgba(239, 68, 68, 0.25);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        /* Upload Modal */
        #upload-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        #upload-modal.show {
            display: flex;
        }

        .modal-content {
            background: var(--sidebar-bg);
            border-radius: 16px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
            float: right;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .dashboard-main {
                margin-left: 0;
            }
        }
    </style>

    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <div class="sidebar-logo">
                <a href="<?= Url::home() ?>" class="sidebar-logo-link">
                    <div class="sidebar-logo-icon">🎤</div>
                    <span>Ovoza</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <a href="<?= Url::to(['transcription/index']) ?>" class="nav-item active">
                        <span class="nav-icon">🏠</span>
                        <span>Bosh sahifa</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Xizmatlar</div>
                    <a href="<?= Url::to(['transcription/index']) ?>" class="nav-item">
                        <span class="nav-icon">🎙️</span>
                        <span>Ovozdan matnga (STT)</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Sozlamalar</div>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">📊</span>
                        <span>Tarix</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">👤</span>
                        <span>Profil</span>
                    </a>
                    <a href="<?= Url::to(['site/logout']) ?>" class="nav-item" data-method="post">
                        <span class="nav-icon">🚪</span>
                        <span>Chiqish</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-wallet">
                <div class="wallet-card">
                    <div class="wallet-label">
                        💰 Balans
                    </div>
                    <div class="wallet-amount">
                        <?= number_format($user->wallet_balance, 2, '.', ' ') ?>
                        <span class="wallet-currency">UZS</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1 class="greeting">
                    <?php
                    $hour = date('H');
                    if ($hour < 12) {
                        echo "Xayrli tong";
                    } elseif ($hour < 18) {
                        echo "Xayrli kun";
                    } else {
                        echo "Xayrli kech";
                    }
                    ?>, <?= Html::encode($user->username) ?>! 👋
                </h1>
                <p class="greeting-subtitle">Ovoza platformasiga xush kelibsiz</p>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card" onclick="openUploadModal()">
                    <div class="action-icon">📁</div>
                    <h3 class="action-title">Fayl yuklash</h3>
                    <p class="action-description">Audio faylni yuklang</p>
                </div>

                <div class="action-card" onclick="alert('Ovoz yozish tez orada!')">
                    <div class="action-icon">🎙️</div>
                    <h3 class="action-title">Ovoz yozish</h3>
                    <p class="action-description">Ovozingizni yozib oling</p>
                </div>
            </div>

            <!-- Jobs Section -->
            <div class="jobs-section">
                <h2 class="section-title">Mening ishlarim</h2>

                <?php if (empty($jobs)): ?>
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                        <p>Hali hech qanday ish yo'q</p>
                        <p style="font-size: 0.875rem;">Audio yuklang va boshlang!</p>
                    </div>
                <?php else: ?>
                    <table class="jobs-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fayl nomi</th>
                            <th>Davomiyligi</th>
                            <th>Narx</th>
                            <th>Holat</th>
                            <th>Sana</th>
                            <th>Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><code style="color: var(--primary-purple);">#<?= $job->id ?></code></td>
                                <td><?= Html::encode($job->audio_filename) ?></td>
                                <td><?= $job->getFormattedDuration() ?></td>
                                <td><?= number_format($job->cost, 2) ?> UZS</td>
                                <td>
                                <span class="status-badge status-<?= $job->status ?>">
                                    <?= $job->getStatusLabel() ?>
                                </span>
                                </td>
                                <td><?= date('d.m.Y H:i', $job->created_at) ?></td>
                                <td>
                                    <a href="<?= Url::to(['view', 'id' => $job->id]) ?>" class="btn-action btn-view">
                                        Ko'rish
                                    </a>
                                    <?php if ($job->canBeCancelled()): ?>
                                        <button class="btn-action btn-cancel cancel-job" data-job-id="<?= $job->id ?>">
                                            Bekor qilish
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Upload Modal -->
    <div id="upload-modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeUploadModal()">×</button>
            <h3 style="margin-bottom: 1.5rem;">Audio yuklash</h3>

            <form id="upload-form" enctype="multipart/form-data">
                <div style="margin-bottom: 1.5rem;">
                    <input type="file" id="audio-file" name="audio" accept=".wav,.mp3,.ogg" required
                           style="width: 100%; padding: 1rem; background: var(--card-bg); border: 2px dashed var(--primary-purple); border-radius: 8px; color: var(--text-light);">
                    <p style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">
                        WAV, MP3, OGG (max 100MB)
                    </p>
                </div>

                <button type="submit" class="btn-action" id="upload-btn" style="width: 100%; background: var(--primary-purple); color: white; padding: 1rem;">
                    Yuklash
                </button>

                <div id="upload-progress" style="display: none; margin-top: 1rem;">
                    <div style="background: var(--card-bg); height: 8px; border-radius: 4px; overflow: hidden;">
                        <div style="background: var(--primary-purple); height: 100%; width: 100%; transition: width 0.3s;" class="progress-bar-animated"></div>
                    </div>
                    <p style="margin-top: 0.5rem; text-align: center; color: var(--text-muted);">Yuklanmoqda...</p>
                </div>

                <div id="upload-result" style="display: none; margin-top: 1rem; padding: 1rem; border-radius: 8px;"></div>
            </form>
        </div>
    </div>

<?php
$uploadUrl = Url::to(['transcription/upload']);
$cancelUrl = Url::to(['transcription/cancel']);

$js = <<<JS
function openUploadModal() {
    document.getElementById('upload-modal').classList.add('show');
}

function closeUploadModal() {
    document.getElementById('upload-modal').classList.remove('show');
}

// Upload form - WORKING CODE from jQuery version!
jQuery(document).ready(function($) {
    $('#upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var uploadBtn = $('#upload-btn');
        var progressDiv = $('#upload-progress');
        var resultDiv = $('#upload-result');
        
        uploadBtn.prop('disabled', true);
        progressDiv.show();
        resultDiv.hide();
        
        $.ajax({
            url: '$uploadUrl',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                progressDiv.hide();
                uploadBtn.prop('disabled', false);
                
                if (response.success) {
                    resultDiv.css({
                        'display': 'block',
                        'background': 'rgba(16, 185, 129, 0.15)',
                        'color': '#34D399'
                    }).html('✅ ' + response.message + '<br>Narx: ' + response.cost + ' UZS');
                    
                    setTimeout(function() {
                        closeUploadModal();
                        location.reload();
                    }, 2000);
                } else {
                    resultDiv.css({
                        'display': 'block',
                        'background': 'rgba(239, 68, 68, 0.15)',
                        'color': '#F87171'
                    }).html('❌ ' + response.message);
                }
            },
            error: function() {
                progressDiv.hide();
                uploadBtn.prop('disabled', false);
                resultDiv.css({
                    'display': 'block',
                    'background': 'rgba(239, 68, 68, 0.15)',
                    'color': '#F87171'
                }).html('❌ Yuklashda xatolik. Qayta urinib ko\'ring.');
            }
        });
    });
    
    // Cancel job - WORKING CODE!
    $('.cancel-job').on('click', function() {
        if (!confirm('Ishni bekor qilmoqchimisiz?')) {
            return;
        }
        
        var jobId = $(this).data('job-id');
        var cancelUrlWithId = '$cancelUrl?id=' + jobId;
        
        $.post(cancelUrlWithId, function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Xato: ' + response.message);
            }
        }).fail(function() {
            alert('So\'rov muvaffaqiyatsiz. Qayta urinib ko\'ring.');
        });
    });
});
JS;

$this->registerJs($js);
?>