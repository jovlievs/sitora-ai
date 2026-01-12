<?php
/**
 * Ultra Simple Test - Complete Task
 * Usage: http://sitora.uz/test-wallet.php?task_id=1
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// Load Yii2
require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../config/bootstrap.php');
require(__DIR__ . '/../../common/config/bootstrap.php');

// Load configuration
$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/main-local.php')
);

// Create application
(new yii\web\Application($config));

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Wallet Deduction</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f5f5f5; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; font-weight: bold; }
    </style>
</head>
<body>

<h1>🧪 Wallet Deduction Test</h1>

<?php
use common\models\Task;
use common\models\WalletTransaction;

$taskId = isset($_GET['task_id']) ? (int)$_GET['task_id'] : null;

if (!$taskId) {
    echo "<h2>Available Tasks:</h2>";

    $tasks = Task::find()
        ->orderBy(['id' => SORT_DESC])
        ->limit(10)
        ->all();

    if (empty($tasks)) {
        echo "<p class='error'>No tasks found! Upload an audio file first.</p>";
        echo "<p><a href='/transcription/index'>Go to Dashboard</a></p>";
        exit;
    }

    echo "<table>";
    echo "<tr><th>Task ID</th><th>Job ID</th><th>Status</th><th>Attempt</th><th>Action</th></tr>";

    foreach ($tasks as $t) {
        $statusColor = $t->status == 'completed' ? 'success' : 'error';
        echo "<tr>";
        echo "<td>{$t->id}</td>";
        echo "<td>{$t->job_id}</td>";
        echo "<td class='{$statusColor}'>{$t->status}</td>";
        echo "<td>{$t->attempt_number}</td>";
        echo "<td><a href='?task_id={$t->id}'>Test This →</a></td>";
        echo "</tr>";
    }

    echo "</table>";
    exit;
}

// Process task
$task = Task::findOne($taskId);

if (!$task) {
    echo "<p class='error'>Task #{$taskId} not found!</p>";
    echo "<p><a href='?'>← Back</a></p>";
    exit;
}

$job = $task->job;
$user = $job->user;

echo "<h2>Task #{$task->id} - Job #{$job->id}</h2>";

// Show BEFORE state
echo "<h3>📊 BEFORE:</h3>";
echo "<table>";
echo "<tr><th>Property</th><th>Value</th></tr>";
echo "<tr><td>Task Status</td><td class='error'>{$task->status}</td></tr>";
echo "<tr><td>Job Status</td><td class='error'>{$job->status}</td></tr>";
echo "<tr><td>Audio ID</td><td>{$job->audio_id}</td></tr>";
echo "<tr><td>Duration</td><td>{$job->audio_duration_seconds} sec</td></tr>";
echo "<tr><td>Job Cost</td><td class='error'>{$job->cost} UZS</td></tr>";
echo "<tr><td>User ({$user->username})</td><td>{$user->id}</td></tr>";
echo "<tr><td>Wallet Balance</td><td class='info'>{$user->wallet_balance} UZS</td></tr>";
echo "</table>";

if ($task->status === 'completed') {
    echo "<p class='error'>⚠️ This task is already completed!</p>";

    $trans = WalletTransaction::find()->where(['job_id' => $job->id])->one();
    if ($trans) {
        echo "<h3>💰 Existing Transaction:</h3>";
        echo "<table>";
        echo "<tr><td>Amount</td><td class='error'>{$trans->amount} UZS</td></tr>";
        echo "<tr><td>Balance Before</td><td>{$trans->balance_before} UZS</td></tr>";
        echo "<tr><td>Balance After</td><td>{$trans->balance_after} UZS</td></tr>";
        echo "</table>";
    }

    echo "<p><a href='?'>← Try Another Task</a></p>";
    exit;
}

// COMPLETE THE TASK
echo "<hr>";
echo "<h3>⚙️ PROCESSING:</h3>";
echo "<p>Calling: <code>\$task->markAsCompleted()</code></p>";

$testText = "Assalomu aleykum! Bu test matni. Sitora ovoz yozuvini matnga aylantirish platformasi muvaffaqiyatli ishlamoqda!";

try {
    $result = $task->markAsCompleted($testText);

    if ($result) {
        echo "<p class='success'>✅ Task completed successfully!</p>";
    } else {
        echo "<p class='error'>❌ Failed to complete task</p>";
        echo "<pre>";
        print_r($task->errors);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Refresh models
$task->refresh();
$job->refresh();
$user->refresh();

// Show AFTER state
echo "<hr>";
echo "<h3>📊 AFTER:</h3>";
echo "<table>";
echo "<tr><th>Property</th><th>Value</th></tr>";
echo "<tr><td>Task Status</td><td class='success'>{$task->status}</td></tr>";
echo "<tr><td>Job Status</td><td class='success'>{$job->status}</td></tr>";
echo "<tr><td>Wallet Balance</td><td class='info'>{$user->wallet_balance} UZS</td></tr>";
echo "<tr><td>Transcription</td><td>" . substr($job->transcription_text, 0, 50) . "...</td></tr>";
echo "</table>";

// Check transaction
$transaction = WalletTransaction::find()
    ->where(['job_id' => $job->id])
    ->one();

if ($transaction) {
    echo "<h3>💰 WALLET TRANSACTION:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>Transaction ID</td><td>{$transaction->id}</td></tr>";
    echo "<tr><td>Amount</td><td class='error'>{$transaction->amount} UZS</td></tr>";
    echo "<tr><td>Type</td><td>{$transaction->transaction_type}</td></tr>";
    echo "<tr><td>Balance Before</td><td>{$transaction->balance_before} UZS</td></tr>";
    echo "<tr><td>Balance After</td><td>{$transaction->balance_after} UZS</td></tr>";
    echo "<tr><td>Description</td><td>{$transaction->description}</td></tr>";
    echo "<tr><td>Created</td><td>" . date('Y-m-d H:i:s', $transaction->created_at) . "</td></tr>";
    echo "</table>";

    $deducted = $transaction->balance_before - $transaction->balance_after;
    echo "<h2 class='success'>✅ SUCCESS! Deducted {$deducted} UZS</h2>";
} else {
    echo "<h3 class='error'>⚠️ NO TRANSACTION FOUND</h3>";
    echo "<p>Possible reasons:</p>";
    echo "<ul>";
    echo "<li>Job cost is 0 UZS (free)</li>";
    echo "<li>User balance was insufficient</li>";
    echo "<li>Wallet deduction failed</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h3>🔗 Quick Links:</h3>";
echo "<ul>";
echo "<li><a href='/transcription/view?id={$job->id}'>View Job in UI</a></li>";
echo "<li><a href='/transcription/index'>Dashboard</a></li>";
echo "<li><a href='?'>Test Another Task</a></li>";
echo "</ul>";
?>

</body>
</html>