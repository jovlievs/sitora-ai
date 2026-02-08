<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $job common\models\TranscriptionJob */

$this->title = 'Job #' . $job->id . ' - ' . $job->audio_id;

$statusLabel = $job->getStatusLabel();
$statusClass = 'pending';

if (in_array($job->status, ['completed','done','ready',2,'tayyor'], true)) $statusClass = 'completed';
elseif (in_array($job->status, ['processing',1], true)) $statusClass = 'processing';
elseif (in_array($job->status, ['failed','error',3], true)) $statusClass = 'failed';
elseif (in_array($job->status, ['cancelled','canceled',4], true)) $statusClass = 'cancelled';

$backUrl = Url::to(['index']);
$downloadUrl = Url::to(['download', 'id' => $job->id]);
$streamUrl = Url::to(['stream', 'id' => $job->id]);
?>

<style>
    :root{
        --dark-bg:#0F0F1E; --sidebar-bg:#1A1A2E; --primary:#7C3AED; --primary2:#A78BFA;
        --text:#E2E8F0; --muted:#94A3B8; --border: rgba(124,58,237,0.16);
    }
    body{background:var(--dark-bg); color:var(--text); margin:0; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}
    .page-wrap{max-width:1100px; margin:0 auto; padding:28px 20px 60px;}
    .topbar{display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px;}
    .back-link{
        display:inline-flex;
        align-items:center;
        gap:10px;
        color:#C7D2FE;
        font-weight:700;
        text-decoration: none;
        padding:10px 16px;
        border-radius:12px;
        background:rgba(99,102,241,0.18);
        border:1px solid rgba(99,102,241,0.45);
        transition:.25s;}
    .back-link:hover{
        color:white; border-color:#818CF8;
        background: rgba(99,102,241,0.28);
        transform:translateY(-2px);}
    .title{font-size:28px; font-weight:900; margin:0 0 6px;}
    .subtitle{margin:0 0 22px; color:var(--muted); font-size:14px;}
    .grid{display:grid; grid-template-columns:1.05fr .95fr; gap:16px;}
    @media(max-width:900px){.grid{grid-template-columns:1fr;}}
    .card{background:var(--sidebar-bg); border:1px solid rgba(124,58,237,0.12); border-radius:16px; padding:18px; box-shadow:0 18px 40px rgba(0,0,0,0.25);}
    .card h3{margin:0 0 12px; font-size:16px; font-weight:900;}
    .kv{display:grid; grid-template-columns:1fr 1fr; gap:12px;}
    @media(max-width:520px){.kv{grid-template-columns:1fr;}}
    .kv-item{background:rgba(255,255,255,0.03); border:1px solid rgba(124,58,237,0.10); border-radius:14px; padding:12px;}
    .kv-label{color:var(--muted); font-size:12px; margin-bottom:6px;}
    .kv-value{font-size:14px; font-weight:800; overflow-wrap:anywhere;}
    .badge{display:inline-flex; align-items:center; gap:8px; font-size:12px; font-weight:900; padding:7px 10px; border-radius:999px; border:1px solid rgba(255,255,255,0.10);}
    .badge.pending{background:rgba(245,158,11,0.12); color:#FCD34D; border-color:rgba(245,158,11,0.25);}
    .badge.processing{background:rgba(59,130,246,0.12); color:#93C5FD; border-color:rgba(59,130,246,0.25);}
    .badge.completed{background:rgba(16,185,129,0.12); color:#6EE7B7; border-color:rgba(16,185,129,0.25);}
    .badge.failed{background:rgba(239,68,68,0.12); color:#FCA5A5; border-color:rgba(239,68,68,0.25);}
    .badge.cancelled{background:rgba(148,163,184,0.12); color:#CBD5E1; border-color:rgba(148,163,184,0.25);}
    .btn{display:inline-flex; align-items:center; justify-content:center; gap:10px; padding:10px 14px; border-radius:12px;
        border:1px solid rgba(124,58,237,0.18); background:rgba(124,58,237,0.14); color:var(--text); cursor:pointer;
        font-weight:900; text-decoration:none; transition:.2s; user-select:none;}
    .btn:hover{border-color:rgba(124,58,237,0.32); background:rgba(124,58,237,0.20); transform:translateY(-1px);}
    .btn.primary{background:var(--primary); border-color:rgba(124,58,237,0.45);}
    .btn.primary:hover{background:var(--primary2);}
    .btn:disabled{opacity:.65; cursor:not-allowed; transform:none;}
    .result-box{background:rgba(255,255,255,0.03); border:1px dashed rgba(124,58,237,0.35); border-radius:16px; padding:14px;}
    .result-head{display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px;}
    .text-area{width:100%; min-height:160px; resize:vertical; background:rgba(255,255,255,0.03); border:1px solid rgba(124,58,237,0.14);
        border-radius:14px; padding:12px; color:var(--text); font-size:14px; line-height:1.55; outline:none;}
    .muted{color:var(--muted); font-size:13px;}

    /* Toast */
    .toast{
        position:fixed;
        right:18px;
        top:18px;
        background:rgba(16,185,129,0.15);
        border:1px solid rgba(16,185,129,0.25);
        color:#6EE7B7;
        padding:12px 14px;
        border-radius:14px;
        font-weight:900;

        opacity:0;
        transform:translateY(-10px);
        transition:.25s;

        z-index:99999;
        pointer-events:none;
    }
    .toast.show{opacity:1; transform:translateY(0);}

    .audio-player{width:100%; margin-top:10px; border-radius:12px; overflow:hidden;}
</style>

<div class="page-wrap">
    <div class="topbar">
        <a class="back-link" href="<?= $backUrl ?>">← Orqaga (Dashboard)</a>
        <span class="badge <?= Html::encode($statusClass) ?>"><?= Html::encode($statusLabel) ?></span>
    </div>

    <h1 class="title"><?= Html::encode($this->title) ?></h1>
    <p class="subtitle">Audio yuklangan job tafsilotlari va transkripsiya natijasi.</p>

    <div class="grid">
        <div class="card">
            <h3>Job ma'lumotlari</h3>

            <div class="kv">
                <div class="kv-item"><div class="kv-label">Job ID</div><div class="kv-value">#<?= (int)$job->id ?></div></div>
                <div class="kv-item"><div class="kv-label">Audio ID</div><div class="kv-value"><?= Html::encode($job->audio_id) ?></div></div>

                <div class="kv-item"><div class="kv-label">Format</div><div class="kv-value"><span class="badge processing"><?= strtoupper(Html::encode($job->audio_format)) ?></span></div></div>
                <div class="kv-item"><div class="kv-label">Til</div><div class="kv-value"><?= Html::encode($job->language ?? 'uz') ?></div></div>

                <div class="kv-item"><div class="kv-label">Davomiyligi</div><div class="kv-value"><?= Html::encode($job->getFormattedDuration()) ?></div></div>
                <div class="kv-item"><div class="kv-label">Hajmi</div><div class="kv-value"><?= Html::encode(number_format((float)$job->audio_size_mb, 2)) ?> MB</div></div>

                <div class="kv-item"><div class="kv-label">Narx</div><div class="kv-value"><?= Html::encode(number_format((float)$job->cost, 2, '.', ' ')) ?> UZS</div></div>
                <div class="kv-item"><div class="kv-label">Yaratilgan</div><div class="kv-value"><?= Html::encode(date('Y-m-d H:i:s', (int)$job->created_at)) ?></div></div>
            </div>

            <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
                <a href="<?= $downloadUrl ?>" class="btn primary">⬇️ Download audio</a>
            </div>


        </div>

        <div class="card">
            <h3>Transkripsiya natijasi</h3>

            <div class="result-box">
                <div class="result-head">
                    <b>📝 Matn</b>
                    <button type="button" class="btn primary" id="btn-copy" aria-label="Copy transcript">📋 Copy</button>
                </div>

                <textarea class="text-area" id="transcript" readonly><?= Html::encode((string)$job->transcription_text) ?></textarea>

                <div style="display:flex; justify-content:space-between; gap:10px; margin-top:10px; flex-wrap:wrap;">
                    <div class="muted">Word count: <b><?= (int)$job->word_count ?></b></div>
                    <div class="muted">Confidence: <b><?= $job->confidence_score !== null ? Html::encode(number_format((float)$job->confidence_score, 2)) : '—' ?></b></div>
                </div>

                <?php if (!$job->transcription_text): ?>
                    <div class="muted" style="margin-top:12px;">Hozircha natija yo'q. "processing" bo'lsa biroz kuting.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast">Copied ✅</div>

<?php
$js = <<<JS
document.addEventListener('DOMContentLoaded', function () {
  const btnCopy = document.getElementById('btn-copy');
  const transcript = document.getElementById('transcript');
  const toast = document.getElementById('toast');

  if (!btnCopy || !transcript || !toast) return;

  function showToast(msg){
    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(window.__toastTimer);
    window.__toastTimer = setTimeout(() => {
      toast.classList.remove('show');
    }, 1600);
  }

  btnCopy.addEventListener('click', async function(){
    const text = transcript.value || '';
    if (!text.trim()){
      showToast("Matn bo'sh");
      return;
    }

    const oldHtml = btnCopy.innerHTML;
    btnCopy.innerHTML = "✅ Muvaffaqiyatli nusxalandi";
    btnCopy.disabled = true;

    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
      } else {
        transcript.focus();
        transcript.select();
        document.execCommand('copy');
        transcript.setSelectionRange(0, 0);
      }
      showToast('✅ Muvaffaqiyatli nusxalandi');
    } catch (e) {
      showToast('Copy failed ❌');
    } finally {
      setTimeout(() => {
        btnCopy.innerHTML = oldHtml;
        btnCopy.disabled = false;
      }, 900);
    }
  });
});
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>
