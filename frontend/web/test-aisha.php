<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../config/bootstrap.php');
require(__DIR__ . '/../../common/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/main-local.php')
);

new yii\web\Application($config);

use common\models\Task;
use common\models\SystemSetting;

$taskId = $_GET['task_id'] ?? 21;
$task = Task::findOne($taskId);

if (!$task) {
    die("Task not found!");
}

echo "<h2>Testing Aisha API Status Check</h2>";
echo "<p><strong>Task ID:</strong> {$task->id}</p>";
echo "<p><strong>Job ID:</strong> {$task->job_id}</p>";
echo "<p><strong>External Task ID:</strong> {$task->external_task_id}</p>";
echo "<p><strong>Current Status:</strong> {$task->status}</p>";
echo "<hr>";

$apiKey = SystemSetting::getValue('aisha_api_key');
$apiBaseUrl = SystemSetting::getValue('aisha_api_url', 'https://back.aisha.group/api/v2/stt');

echo "<h3>API Configuration:</h3>";
echo "<p><strong>API Key:</strong> " . (substr($apiKey, 0, 10) . "...") . "</p>";
echo "<p><strong>API URL:</strong> {$apiBaseUrl}</p>";
echo "<hr>";

// Test API call
$transcriptId = $task->external_task_id;
$url = $apiBaseUrl . '/get/' . $transcriptId . '/';

echo "<h3>Calling Aisha API:</h3>";
echo "<p><strong>URL:</strong> {$url}</p>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'x-api-key: ' . $apiKey,
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> {$httpCode}</p>";

if ($curlError) {
    echo "<p style='color:red'><strong>cURL Error:</strong> {$curlError}</p>";
}

echo "<h3>Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

if ($httpCode === 200) {
    $responseData = json_decode($response, true);
    echo "<h3>Parsed Response:</h3>";
    echo "<pre>" . print_r($responseData, true) . "</pre>";

    if (isset($responseData['status']) && $responseData['status'] === 'completed') {
        echo "<p style='color:green; font-size:20px'><strong>✅ Transcription is COMPLETED on Aisha!</strong></p>";

        if (isset($responseData['text'])) {
            echo "<h3>Transcription Text:</h3>";
            echo "<div style='border:1px solid #ccc; padding:10px; background:#f5f5f5'>";
            echo htmlspecialchars($responseData['text']);
            echo "</div>";
        }
    }
}
?>
<!--```-->
<!---->
<!------->
<!---->
<!--## 🧪 RUN THE TEST:-->
<!---->
<!--Visit:-->
<!--```-->
<!--http://sitora.uz/test-aisha.php?task_id=21-->