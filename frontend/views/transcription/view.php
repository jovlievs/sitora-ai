<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $job common\models\TranscriptionJob */

$this->title = 'Job #' . $job->id . ' - ' . $job->audio_id;
$this->params['breadcrumbs'][] = ['label' => 'Transcriptions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="transcription-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Job Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Job ID</th>
                            <td><?= $job->id ?></td>
                        </tr>
                        <tr>
                            <th>Audio ID</th>
                            <td><code><?= Html::encode($job->audio_id) ?></code></td>
                        </tr>
                        <tr>
                            <th>Filename</th>
                            <td><?= Html::encode($job->audio_filename) ?></td>
                        </tr>
                        <tr>
                            <th>Format</th>
                            <td><span class="badge bg-secondary"><?= strtoupper($job->audio_format) ?></span></td>
                        </tr>
                        <tr>
                            <th>Duration</th>
                            <td><?= $job->getFormattedDuration() ?> (<?= $job->audio_duration_seconds ?>s)</td>
                        </tr>
                        <tr>
                            <th>File Size</th>
                            <td><?= number_format($job->audio_size_mb, 2) ?> MB</td>
                        </tr>
                        <tr>
                            <th>Cost</th>
                            <td><strong><?= number_format($job->cost, 2) ?> UZS</strong></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?= $job->getStatusColor() ?>" id="job-status">
                                    <?= $job->getStatusLabel() ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td><?= date('Y-m-d H:i:s', $job->created_at) ?></td>
                        </tr>
                        <?php if ($job->completed_at): ?>
                        <tr>
                            <th>Completed At</th>
                            <td><?= date('Y-m-d H:i:s', $job->completed_at) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Transcription Result</h5>
                </div>
                <div class="card-body" id="result-container">
                    <?php if ($job->status === 'completed'): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> Transcription completed!
                        </div>
                        
                        <div class="transcription-text p-3 border rounded bg-light">
                            <?= Html::encode($job->transcription_text) ?>
                        </div>
                        
                        <?php if ($job->confidence_score): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                Confidence: <?= round($job->confidence_score * 100, 2) ?>%
                            </small>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($job->word_count): ?>
                        <div class="mt-1">
                            <small class="text-muted">
                                Word count: <?= $job->word_count ?>
                            </small>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <button class="btn btn-sm btn-primary" onclick="copyToClipboard()">
                                <i class="bi bi-clipboard"></i> Copy Text
                            </button>
                        </div>
                        
                    <?php elseif ($job->status === 'processing'): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-hourglass-split"></i> Processing your audio...
                        </div>
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Please wait, this may take a few minutes...</p>
                        </div>
                        
                    <?php elseif ($job->status === 'pending'): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-clock"></i> Job is queued and waiting to be processed...
                        </div>
                        
                    <?php elseif ($job->status === 'failed'): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle"></i> Transcription failed
                        </div>
                        <?php if ($job->latestTask && $job->latestTask->error_message): ?>
                        <p><strong>Error:</strong> <?= Html::encode($job->latestTask->error_message) ?></p>
                        <?php endif; ?>
                        
                    <?php elseif ($job->status === 'cancelled'): ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-slash-circle"></i> Job was cancelled
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Task History -->
            <?php if (!empty($job->tasks)): ?>
            <div class="card">
                <div class="card-header">
                    <h6>Task History</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Attempt</th>
                                <th>Status</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($job->tasks as $task): ?>
                            <tr>
                                <td><?= $task->attempt_number ?></td>
                                <td>
                                    <span class="badge bg-<?= $task->getStatusColor() ?>">
                                        <?= $task->getStatusLabel() ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $task->processing_duration_seconds ? $task->processing_duration_seconds . 's' : '-' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3">
        <?= Html::a('<i class="bi bi-arrow-left"></i> Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
        
        <?php if ($job->canBeCancelled()): ?>
        <button class="btn btn-danger" id="cancel-btn">
            <i class="bi bi-x-circle"></i> Cancel Job
        </button>
        <?php endif; ?>
    </div>
</div>

<?php
$statusUrl = Url::to(['status', 'id' => $job->id]);
$cancelUrl = Url::to(['cancel', 'id' => $job->id]);

$js = <<<JS
// Auto-refresh status if job is pending or processing
var currentStatus = '{$job->status}';

if (currentStatus === 'pending' || currentStatus === 'processing') {
    var pollInterval = setInterval(function() {
        $.get('{$statusUrl}', function(response) {
            if (response.status !== currentStatus) {
                // Status changed, reload page
                location.reload();
            }
            
            // Update status badge
            $('#job-status').text(response.statusLabel);
        });
    }, 5000); // Poll every 5 seconds
}

// Cancel button
$('#cancel-btn').on('click', function() {
    if (!confirm('Are you sure you want to cancel this job?')) {
        return;
    }
    
    $.post('{$cancelUrl}', function(response) {
        if (response.success) {
            alert(response.message);
            location.reload();
        } else {
            alert('Error: ' + response.message);
        }
    });
});

// Copy to clipboard function
function copyToClipboard() {
    var text = $('.transcription-text').text();
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard!');
    });
}
JS;

$this->registerJs($js);
?>
