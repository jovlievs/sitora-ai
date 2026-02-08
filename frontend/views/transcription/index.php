<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $jobs common\models\TranscriptionJob[] */

$this->title = 'Dashboard - Ovoza';
$user  = Yii::$app->user->identity;
$route = Yii::$app->controller->route; // e.g. transcription/index

// URLS
$uploadUrl  = Url::to(['transcription/upload']);
$cancelUrl  = Url::to(['transcription/cancel']);
$streamTpl  = Url::to(['transcription/stream', 'id' => '__ID__']); // template
?>

    <style>
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
            background: transparent;
            border: 0;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font: inherit;
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

        .dashboard-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .greeting {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .greeting-subtitle {
            color: var(--text-muted);
        }

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

        .jobs-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .job-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .job-item:hover {
            background: rgba(124, 58, 237, 0.1);
            transform: translateX(4px);
        }

        .play-btn {
            width: 40px;
            height: 40px;
            min-width: 40px;
            background: rgba(255, 255, 255, 0.08);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-light);
            font-size: 1rem;
        }

        .play-btn:hover {
            background: var(--primary-purple);
            transform: scale(1.1);
        }

        .job-info { flex: 1; min-width: 0; }

        .job-inline {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            min-width: 0;
        }

        .job-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-light);
            margin: 0;
            white-space: nowrap;
        }

        .job-meta {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 14px;
            white-space: nowrap;
            min-width: 0;
            opacity: .85;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .status-badge-compact {
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            flex: 0 0 auto;
        }

        .status-completed { background: rgba(16, 185, 129, 0.15); color: #34D399; }
        .status-processing { background: rgba(59, 130, 246, 0.15); color: #60A5FA; }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: #FBBF24; }
        .status-failed { background: rgba(239, 68, 68, 0.15); color: #F87171; }
        .status-cancelled { background: rgba(148,163,184,0.15); color: #CBD5E1; }

        .view-btn{
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 36px;
            padding: 0 12px;
            min-width: 92px;
            border-radius: 999px;
            background: rgba(124,58,237,0.22);
            border: 1px solid rgba(124,58,237,0.45);
            color: #EDE9FE;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
            transition: .2s;
        }

        .view-btn:hover{
            background: rgba(124,58,237,0.32);
            border-color: rgba(124,58,237,0.65);
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        #upload-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        #upload-modal.show { display: flex; }

        .modal-content {
            background: var(--sidebar-bg);
            border-radius: 16px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 2rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        #list-player {
            position: absolute;
            width: 0;
            height: 0;
            opacity: 0;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .dashboard-sidebar { transform: translateX(-100%); }
            .dashboard-main { margin-left: 0; }
        }
    </style>

    <div class="dashboard-wrapper">
        <aside class="dashboard-sidebar">
            <div class="sidebar-logo">
                <a href="<?= Url::to(['transcription/index']) ?>" class="sidebar-logo-link">
                    <div class="sidebar-logo-icon">🎙️</div>
                    <span>Ovoza</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Menyu</div>

                    <a href="<?= Url::to(['transcription/index']) ?>"
                       class="nav-item <?= $route === 'transcription/index' ? 'active' : '' ?>">
                        <span>🏠</span>
                        <span>Bosh sahifa</span>
                    </a>

                    <a href="<?= Url::to(['transcription/history']) ?>"
                       class="nav-item <?= $route === 'transcription/history' ? 'active' : '' ?>">
                        <span>📊</span>
                        <span>Tarix</span>
                    </a>

                    <a href="<?= Url::to(['profile/index']) ?>"
                       class="nav-item <?= strpos($route, 'profile/') === 0 ? 'active' : '' ?>">
                        <span>👤</span>
                        <span>Profil</span>
                    </a>

                    <?= Html::beginForm(['site/logout'], 'post', ['style' => 'margin-top:6px;']) ?>
                    <button type="submit" class="nav-item">
                        <span>🚪</span>
                        <span>Chiqish</span>
                    </button>
                    <?= Html::endForm() ?>
                </div>
            </nav>

            <div class="sidebar-wallet">
                <div class="wallet-card">
                    <div class="wallet-label">💰 Balans</div>
                    <div class="wallet-amount">
                        <?= number_format((float)$user->wallet_balance, 2, '.', ' ') ?>
                        <span class="wallet-currency">UZS</span>
                    </div>
                </div>
            </div>
        </aside>

        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1 class="greeting">
                    <?php
                    $hour = (int)date('H');
                    if ($hour < 12) echo "Xayrli tong";
                    elseif ($hour < 18) echo "Xayrli kun";
                    else echo "Xayrli kech";
                    ?>, <?= Html::encode($user->username) ?>! 👋
                </h1>
                <p class="greeting-subtitle">Ovoza platformasiga xush kelibsiz</p>
            </div>

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

            <div class="jobs-section">
                <h2 class="section-title">Mening ishlarim</h2>

                <?php if (empty($jobs)): ?>
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                        <p>Hali hech qanday ish yo'q</p>
                        <p style="font-size: 0.875rem;">Audio yuklang va boshlang!</p>
                    </div>
                <?php else: ?>
                    <div class="jobs-list">
                        <?php foreach ($jobs as $job): ?>
                            <?php
                            $langLabel = strtoupper((string)($job->language ?? 'UZ'));

                            $statusRaw = (string)$job->status;
                            $st = 'pending';
                            if (in_array($statusRaw, ['completed','done','ready','success', '2', 2, 'tayyor'], true)) $st = 'completed';
                            elseif (in_array($statusRaw, ['processing', '1', 1], true)) $st = 'processing';
                            elseif (in_array($statusRaw, ['failed','error', '3', 3], true)) $st = 'failed';
                            elseif (in_array($statusRaw, ['cancelled','canceled', '4', 4], true)) $st = 'cancelled';

                            $viewUrl = Url::to(['view', 'id' => (int)$job->id]);
                            ?>

                            <div class="job-item" onclick="window.location='<?= $viewUrl ?>'">
                                <button class="play-btn" type="button"
                                        onclick="event.stopPropagation(); playAudio(<?= (int)$job->id ?>)">
                                    ▶
                                </button>

                                <div class="job-info">
                                    <div class="job-inline">
                                        <h4 class="job-title">Audio #<?= (int)$job->id ?></h4>

                                        <div class="job-meta" title="<?= Html::encode($langLabel) ?>">
                                            <span><?= Html::encode(date('M j, Y', (int)$job->created_at)) ?></span>
                                            <span><?= Html::encode(date('h:i A', (int)$job->created_at)) ?></span>
                                            <span><?= Html::encode($langLabel) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <span class="status-badge-compact status-<?= Html::encode($st) ?>">
                                <?= $st === 'completed' ? 'SUCCESS' : strtoupper($st) ?>
                            </span>

                                <a class="view-btn"
                                   href="<?= $viewUrl ?>"
                                   onclick="event.stopPropagation();"
                                   title="Ko'rish">Ko'rish</a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <audio id="list-player" preload="none"></audio>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="upload-modal">
        <div class="modal-content">
            <button class="modal-close" type="button" onclick="closeUploadModal()">×</button>
            <h3 style="margin-bottom: 1.5rem;">Audio yuklash</h3>

            <form id="upload-modal-form" action="<?= $uploadUrl ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                <div style="margin-bottom: 1.5rem;">
                    <input type="file" id="audioFile" name="audioFile" accept=".wav,.mp3,.ogg" required
                           style="width: 100%; padding: 1rem; background: var(--card-bg); border: 2px dashed var(--primary-purple); border-radius: 8px; color: var(--text-light);">
                    <p style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">
                        WAV, MP3, OGG (max 100MB)
                    </p>
                </div>

                <button type="submit" id="upload-btn"
                        style="width: 100%; background: var(--primary-purple); color: white; padding: 1rem; border:0; border-radius:12px; font-weight:700; cursor:pointer;">
                    Yuklash
                </button>

                <div id="upload-progress" style="display: none; margin-top: 1rem;">
                    <div style="background: var(--card-bg); height: 8px; border-radius: 4px; overflow: hidden;">
                        <div style="background: var(--primary-purple); height: 100%; width: 100%; transition: width 0.3s;"></div>
                    </div>
                    <p style="margin-top: 0.5rem; text-align: center; color: var(--text-muted);">Yuklanmoqda...</p>
                </div>

                <div id="upload-result" style="display: none; margin-top: 1rem; padding: 1rem; border-radius: 8px;"></div>
            </form>
        </div>
    </div>

<?php
$js = <<<JS
window.openUploadModal = function() {
  var modal = document.getElementById('upload-modal');
  if (modal) modal.classList.add('show');
};

window.closeUploadModal = function() {
  var modal = document.getElementById('upload-modal');
  if (modal) modal.classList.remove('show');
};

window.playAudio = function(jobId) {
  var player = document.getElementById('list-player');
  if (!player) return;

  var url = "{$streamTpl}".replace('__ID__', jobId);
  player.dataset.currentId = String(jobId);
  player.src = url;

  player.load();
  player.play().catch(function(err) {
    console.error('play() error', err);
    alert("Brauzer audio'ni avtomatik boshlashga ruxsat bermadi. Yana bir marta bosing.");
  });
};

jQuery(document).ready(function($) {
  $('#upload-modal-form').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var uploadBtn = $('#upload-btn');
    var progressDiv = $('#upload-progress');
    var resultDiv = $('#upload-result');

    uploadBtn.prop('disabled', true);
    progressDiv.show();
    resultDiv.hide();

    $.ajax({
      url: '{$uploadUrl}',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        progressDiv.hide();
        uploadBtn.prop('disabled', false);

        if (response && response.success) {
          var costHtml = response.cost ? '<br>Narx: ' + response.cost + ' UZS' : '';
          resultDiv.css({'display': 'block', 'background': 'rgba(16, 185, 129, 0.15)', 'color': '#34D399'})
                   .html('✅ ' + response.message + costHtml);
          setTimeout(function() {
            window.closeUploadModal();
            location.reload();
          }, 1200);
        } else {
          var msg = (response && response.message) ? response.message : 'Server xatosi';
          resultDiv.css({'display': 'block', 'background': 'rgba(239, 68, 68, 0.15)', 'color': '#F87171'})
                   .html('❌ ' + msg);
        }
      },
      error: function() {
        progressDiv.hide();
        uploadBtn.prop('disabled', false);
        resultDiv.css({'display': 'block', 'background': 'rgba(239, 68, 68, 0.15)', 'color': '#F87171'})
                 .html('❌ Yuklashda xatolik');
      }
    });
  });
});
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>