<?php
/**
 * Simple Test Script: Complete Transcription Job
 * Usage: http://sitora.uz/test-simple.php?task_id=1
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Loading Yii2...</h1>";

try {
    // Load Yii2
    require(__DIR__ . '/../config/bootstrap.php');
    require(__DIR__ . '/../../vendor/autoload.php');
    require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

    echo "<p>✓ Yii2 files loaded</p>";

    // Load config
    $config = yii\helpers\ArrayHelper::merge(
        require(__DIR__ . '/../../common/config/main.php'),
        require(__DIR__ . '/../../common/config/main-local.php'),
        require(__DIR__ . '/../config/main.php'),
        require(__DIR__ . '/../config/main-local.php')
    );

    echo "<p>✓ Config loaded</p>";

    // Create application
    new yii\web\Application($config);

    echo "<p>✓ Application initialized</p>";
    echo "<hr>";

} catch (Exception $e) {
    echo "<p style='color:red'>ERROR loading Yii2:</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Now use the models
use common\models\Task;
use common\models\TranscriptionJob;
use common\models\WalletTransaction;

echo "<h2>Finding Tasks...</h2>";

// Get task ID from URL
$taskId = isset($_GET['task_id']) ? (int)$_GET['task_id'] : null;

if (!$taskId) {
    // Show available tasks
    echo "<p>Available tasks:</p>";

    try {
        $tasks = Task::find()
            ->orderBy(['id' => SORT_DESC])
            ->limit(10)
            ->all();

        if (empty($tasks)) {
            echo "<p>No tasks found! Upload an audio file first.</p>";
            exit;
        }

        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Task ID</th><th>Job ID</th><th>Status</th><th>Action</th></tr>";

        foreach ($tasks as $t) {
            $color = $t->status == 'completed' ? 'green' : 'orange';
            echo "<tr>";
            echo "<td>{$t->id}</td>";
            echo "<td>{$t->job_id}</td>";
            echo "<td style='color:{$color}'>{$t->status}</td>";
            echo "<td><a href='?task_id={$t->id}'>Complete This</a></td>";
            echo "</tr>";
        }

        echo "</table>";

    } catch (Exception $e) {
        echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    }

    exit;
}

// Process specific task
echo "<h2>Processing Task #{$taskId}...</h2>";

try {
    $task = Task::findOne($taskId);

    if (!$task) {
        echo "<p style='color:red'>Task #{$taskId} not found!</p>";
        echo "<p><a href='?'>Go back</a></p>";
        exit;
    }

    $job = $task->job;
    $user = $job->user;

    echo "<h3>Before:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Task Status</th><td>{$task->status}</td></tr>";
    echo "<tr><th>Job Status</th><td>{$job->status}</td></tr>";
    echo "<tr><th>User Balance</th><td style='color:blue; font-weight:bold'>{$user->wallet_balance} UZS</td></tr>";
    echo "<tr><th>Job Cost</th><td style='color:red; font-weight:bold'>{$job->cost} UZS</td></tr>";
    echo "</table>";

    if ($task->status === 'completed') {
        echo "<p style='color:orange'>⚠️ This task is already completed!</p>";
        echo "<p><a href='?'>Try another task</a></p>";
        exit;
    }

    echo "<h3>Completing task...</h3>";

    // Complete the task
    $transcriptionText = "Assalomu aleykum! Bu test matni. Sitora platformasi ishlamoqda.";

    $result = $task->markAsCompleted($transcriptionText);

    if (!$result) {
        echo "<p style='color:red'>Failed to complete task!</p>";
        echo "<pre>";
        print_r($task->errors);
        echo "</pre>";
        exit;
    }

    // Refresh data
    $task->refresh();
    $job->refresh();
    $user->refresh();

    echo "<p style='color:green'>✓ Task completed successfully!</p>";

    echo "<h3>After:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Task Status</th><td style='color:green'>{$task->status}</td></tr>";
    echo "<tr><th>Job Status</th><td style='color:green'>{$job->status}</td></tr>";
    echo "<tr><th>User Balance</th><td style='color:blue; font-weight:bold'>{$user->wallet_balance} UZS</td></tr>";
    echo "</table>";

    // Check for wallet transaction
    $transaction = WalletTransaction::find()
        ->where(['job_id' => $job->id])
        ->one();

    if ($transaction) {
        echo "<h3>💰 Wallet Transaction:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Amount</th><td style='color:red; font-weight:bold'>{$transaction->amount} UZS</td></tr>";
        echo "<tr><th>Balance Before</th><td>{$transaction->balance_before} UZS</td></tr>";
        echo "<tr><th>Balance After</th><td>{$transaction->balance_after} UZS</td></tr>";
        echo "<tr><th>Description</th><td>{$transaction->description}</td></tr>";
        echo "</table>";

        echo "<p style='color:green; font-size:20px'>✅ <strong>Wallet deducted successfully!</strong></p>";
    } else {
        echo "<p style='color:orange'>⚠️ No wallet transaction found (cost may be 0)</p>";
    }

    echo "<hr>";
    echo "<p><a href='/transcription/view?id={$job->id}'>View Job in UI</a> | ";
    echo "<a href='/transcription/index'>Dashboard</a> | ";
    echo "<a href='?'>Test Another</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red'><strong>Error:</strong></p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}