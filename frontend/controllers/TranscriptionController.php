<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use common\models\TranscriptionJob;
use common\models\Task;
use common\models\User;
use common\models\SystemSetting;

/**
 * TranscriptionController handles all STT operations
 * Supports multiple STT providers (Aisha, Sitora)
 */
class TranscriptionController extends Controller
{
    // Use custom layout without Yii2 header!
    public $layout = 'dashboard';

    public function beforeAction($action)
    {
        // Disable CSRF validation for upload and status actions (to allow guest access)
        if (in_array($action->id, ['upload', 'status'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'cancel'], // Removed 'status' to allow guest access
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                // Redirect to transcription/index after login
                'denyCallback' => function () {
                    return Yii::$app->response->redirect(['site/login']);
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'upload' => ['post'],
                    'cancel' => ['post'],
                ],
            ],
        ];
    }


    /**
     * Dashboard - List all user's transcription jobs
     */
    public function actionIndex()
    {
        $jobs = TranscriptionJob::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'jobs' => $jobs,
        ]);
    }

    /**
     * Upload audio file and create transcription job
     * Supports guest uploads (3 free trials)
     */
    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $audioFile = UploadedFile::getInstanceByName('audio');
            $isGuest = Yii::$app->request->post('guest') === '1';

            if (!$audioFile) {
                throw new BadRequestHttpException('No audio file uploaded');
            }

            // For guest uploads, use a temporary guest user or process without user
            if ($isGuest && Yii::$app->user->isGuest) {
                return $this->processGuestUpload($audioFile);
            }

            // Regular authenticated user upload
            $user = User::findOne(Yii::$app->user->id);

            // Validate audio file
            $validation = $this->validateAudioFile($audioFile);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['error'],
                ];
            }

            // Get audio duration
            $duration = $this->getAudioDuration($audioFile->tempName);
            if ($duration === false) {
                return [
                    'success' => false,
                    'message' => 'Failed to read audio file duration',
                ];
            }

            // Calculate cost
            $cost = TranscriptionJob::calculateCost($duration, $user);

            // Check user balance
            if (!$user->hasSufficientBalance($cost)) {
                return [
                    'success' => false,
                    'message' => "Mablag' yetarli emas. Kerak: {$cost} UZS, Mavjud: {$user->wallet_balance} UZS",
                    'required_balance' => $cost,
                    'current_balance' => $user->wallet_balance,
                ];
            }

            // Generate audio_id
            $audioId = TranscriptionJob::generateAudioId();

            // Save audio file
            $savedPath = $this->saveAudioFile($audioFile, $audioId);
            if (!$savedPath) {
                throw new \Exception('Failed to save audio file');
            }

            // Create transcription job
            $job = new TranscriptionJob([
                'user_id' => $user->id,
                'audio_id' => $audioId,
                'audio_filename' => $audioFile->name,
                'audio_path' => $savedPath,
                'audio_format' => $audioFile->extension,
                'audio_duration_seconds' => $duration,
                'audio_size_mb' => round($audioFile->size / 1048576, 3),
                'cost' => $cost,
                'status' => TranscriptionJob::STATUS_PENDING,
            ]);

            if (!$job->save()) {
                $errors = json_encode($job->errors);
                Yii::error("Failed to create job: " . $errors);
                throw new \Exception('Failed to create transcription job. Errors: ' . $errors);
            }

            // Create task
            $task = new Task([
                'job_id' => $job->id,
                'status' => Task::STATUS_QUEUED,
            ]);

            if (!$task->save()) {
                Yii::error("Failed to create task: " . json_encode($task->errors));
                throw new \Exception('Failed to create task');
            }

            // Send to STT provider (Aisha or Sitora)
            $this->sendToSttProvider($job, $task);

            return [
                'success' => true,
                'message' => 'Audio muvaffaqiyatli yuklandi',
                'jobId' => $job->id,
                'audioId' => $audioId,
                'cost' => $cost,
                'duration' => $duration,
            ];

        } catch (\Exception $e) {
            Yii::error("Upload error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Xato: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process guest upload (free trial)
     */
    protected function processGuestUpload($audioFile)
    {
        try {
            // Validate audio file
            $validation = $this->validateAudioFile($audioFile);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['error'],
                ];
            }

            // Get audio duration
            $duration = $this->getAudioDuration($audioFile->tempName);
            if ($duration === false) {
                return [
                    'success' => false,
                    'message' => 'Audio faylni o\'qib bo\'lmadi',
                ];
            }

            // Limit guest uploads to 2 minutes
            if ($duration > 120) {
                return [
                    'success' => false,
                    'message' => 'Bepul sinov uchun maksimal 2 daqiqa. Ro\'yxatdan o\'ting!',
                ];
            }

            // Generate unique audio_id with timestamp to avoid duplicates
            $audioId = 'GUEST_' . time() . '_' . substr(md5(uniqid()), 0, 8);

            // Save audio file
            $savedPath = $this->saveAudioFile($audioFile, $audioId);
            if (!$savedPath) {
                throw new \Exception('Failed to save audio file');
            }

            // Create guest job (user_id = NULL for guests)
            $job = new TranscriptionJob([
                'user_id' => null, // Guest upload
                'audio_id' => $audioId,
                'audio_filename' => $audioFile->name,
                'audio_path' => $savedPath,
                'audio_format' => $audioFile->extension,
                'audio_duration_seconds' => $duration,
                'audio_size_mb' => round($audioFile->size / 1048576, 3),
                'cost' => 0, // Free for guests!
                'status' => TranscriptionJob::STATUS_PENDING,
            ]);

            if (!$job->save()) {
                $errors = json_encode($job->errors);
                Yii::error("Failed to create guest job: " . $errors);
                throw new \Exception('Failed to create transcription job. Errors: ' . $errors);
            }

            // Create task
            $task = new Task([
                'job_id' => $job->id,
                'status' => Task::STATUS_QUEUED,
            ]);

            if (!$task->save()) {
                Yii::error("Failed to create task: " . json_encode($task->errors));
                throw new \Exception('Failed to create task');
            }

            // Send to STT provider
            $this->sendToSttProvider($job, $task);

            // Store guest job ID in session for access
            $session = Yii::$app->session;
            $guestJobs = $session->get('guest_jobs', []);
            $guestJobs[] = $job->id;
            $session->set('guest_jobs', $guestJobs);

            return [
                'success' => true,
                'message' => 'Audio muvaffaqiyatli yuklandi',
                'jobId' => $job->id,
                'audioId' => $audioId,
                'cost' => 0,
                'duration' => $duration,
                'isGuest' => true,
            ];

        } catch (\Exception $e) {
            Yii::error("Guest upload error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Xato: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check job status (for frontend polling)
     * Accessible by guests for their own jobs
     */
    public function actionStatus($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // For logged-in users
        if (!Yii::$app->user->isGuest) {
            $job = TranscriptionJob::findOne([
                'id' => $id,
                'user_id' => Yii::$app->user->id,
            ]);
        } else {
            // For guests - check if they have access via session
            $session = Yii::$app->session;
            $guestJobs = $session->get('guest_jobs', []);

            if (!in_array($id, $guestJobs)) {
                throw new NotFoundHttpException('Job not found');
            }

            $job = TranscriptionJob::findOne($id);
        }

        if (!$job) {
            throw new NotFoundHttpException('Job not found');
        }

        // Get latest task
        $task = $job->latestTask;

        // If task is processing, poll STT provider for result
        if ($task && $task->status === Task::STATUS_PROCESSING && $task->external_task_id) {
            $this->checkSttProviderStatus($task);

            // Refresh models
            $task->refresh();
            $job->refresh();
        }

        return [
            'jobId' => $job->id,
            'status' => $job->status,
            'statusLabel' => $job->getStatusLabel(),
            'transcriptionText' => $job->transcription_text,
            'duration' => $job->getFormattedDuration(),
            'cost' => $job->cost,
            'createdAt' => date('Y-m-d H:i:s', $job->created_at),
            'completedAt' => $job->completed_at ? date('Y-m-d H:i:s', $job->completed_at) : null,
            'taskStatus' => $task ? $task->status : null,
            'taskAttempt' => $task ? $task->attempt_number : null,
        ];
    }

    /**
     * View single job details
     */
    public function actionView($id)
    {
        $job = TranscriptionJob::findOne([
            'id' => $id,
            'user_id' => Yii::$app->user->id,
        ]);

        if (!$job) {
            throw new NotFoundHttpException('Job not found');
        }

        return $this->render('view', [
            'job' => $job,
        ]);
    }

    /**
     * Cancel a pending/processing job
     */
    public function actionCancel($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $job = TranscriptionJob::findOne([
            'id' => $id,
            'user_id' => Yii::$app->user->id,
        ]);

        if (!$job) {
            return ['success' => false, 'message' => 'Job topilmadi'];
        }

        if (!$job->canBeCancelled()) {
            return ['success' => false, 'message' => 'Jobni bekor qilib bo\'lmaydi'];
        }

        if ($job->cancel()) {
            return ['success' => true, 'message' => 'Job bekor qilindi'];
        }

        return ['success' => false, 'message' => 'Jobni bekor qilishda xatolik'];
    }

    // ========================================================================
    // STT PROVIDER ABSTRACTION (Flexible!)
    // ========================================================================

    /**
     * Get current STT provider (aisha or sitora)
     */
    protected function getSttProvider()
    {
        return SystemSetting::getValue('stt_provider', 'aisha');
    }

    /**
     * Send audio to current STT provider
     */
    protected function sendToSttProvider($job, $task)
    {
        $provider = $this->getSttProvider();

        if ($provider === 'aisha') {
            $this->sendToAishaApi($job, $task);
        } elseif ($provider === 'sitora') {
            $this->sendToSitoraApi($job, $task);
        } else {
            Yii::error("Unknown STT provider: {$provider}");
            $task->markAsFailed("Unknown STT provider: {$provider}", 'CONFIG_ERROR');
        }
    }

    /**
     * Check transcription status from current STT provider
     */
    protected function checkSttProviderStatus($task)
    {
        $provider = $this->getSttProvider();

        if ($provider === 'aisha') {
            $this->checkAishaApiStatus($task);
        } elseif ($provider === 'sitora') {
            $this->checkSitoraApiStatus($task);
        }
    }

    // ========================================================================
    // AISHA API INTEGRATION
    // ========================================================================

    /**
     * Send audio to Aisha API for transcription
     */
    protected function sendToAishaApi($job, $task)
    {
        $apiKey = SystemSetting::getValue('aisha_api_key');
        $apiBaseUrl = SystemSetting::getValue('aisha_api_url', 'https://back.aisha.group/api/v2/stt');

        if (!$apiKey) {
            Yii::error("Aisha API key not configured!");
            $task->markAsFailed('Aisha API key not configured', 'CONFIG_ERROR');
            return;
        }

        $audioFullPath = Yii::getAlias('@frontend/web') . $job->audio_path;

        if (!file_exists($audioFullPath)) {
            Yii::error("Audio file not found: {$audioFullPath}");
            $task->markAsFailed('Audio file not found', 'FILE_ERROR');
            return;
        }

        try {
            $cFile = new \CURLFile($audioFullPath, 'audio/' . $job->audio_format, $job->audio_filename);

            $postData = [
                'audio' => $cFile,
                'language' => 'uz',
            ];

            $ch = curl_init($apiBaseUrl . '/post/');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'x-api-key: ' . $apiKey,
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new \Exception("cURL error: {$curlError}");
            }

            if ($httpCode !== 200) {
                throw new \Exception("Aisha API returned HTTP {$httpCode}: {$response}");
            }

            $responseData = json_decode($response, true);

            if (!isset($responseData['id'])) {
                throw new \Exception("Invalid response from Aisha API: " . $response);
            }

            $transcriptId = $responseData['id'];
            $task->external_task_id = $transcriptId;
            $task->markAsProcessing($transcriptId, 'aisha.group');
            $job->markAsProcessing();

            Yii::info("Sent job #{$job->id} to Aisha API, transcript_id: {$transcriptId}");

        } catch (\Exception $e) {
            Yii::error("Failed to send to Aisha API: " . $e->getMessage());
            $task->markAsFailed('Failed to connect to Aisha API: ' . $e->getMessage(), 'API_ERROR');
        }
    }

    /**
     * Check transcription status from Aisha API
     */
    protected function checkAishaApiStatus($task)
    {
        $apiKey = SystemSetting::getValue('aisha_api_key');
        $apiBaseUrl = SystemSetting::getValue('aisha_api_url', 'https://back.aisha.group/api/v2/stt');

        if (!$task->external_task_id) {
            return;
        }

        try {
            $transcriptId = $task->external_task_id;

            $ch = curl_init($apiBaseUrl . '/get/' . $transcriptId . '/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'x-api-key: ' . $apiKey,
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Yii::warning("Aisha API status check failed: HTTP {$httpCode}");
                return;
            }

            $responseData = json_decode($response, true);

            // Aisha API uses 'SUCCESS' status and 'transcript' field
            if (isset($responseData['status']) && $responseData['status'] === 'SUCCESS') {
                if (isset($responseData['transcript'])) {
                    $task->markAsCompleted($responseData['transcript']);
                    Yii::info("Task #{$task->id} completed via Aisha API");
                }
            } elseif (isset($responseData['status']) &&
                ($responseData['status'] === 'FAILED' || $responseData['status'] === 'failed')) {
                $errorMessage = $responseData['error'] ?? 'Unknown error';
                $task->markAsFailed($errorMessage, 'AISHA_API_FAILED');
            }

        } catch (\Exception $e) {
            Yii::error("Error checking Aisha API status: " . $e->getMessage());
        }
    }

    // ========================================================================
    // SITORA API INTEGRATION (Your own API)
    // ========================================================================

    /**
     * Send audio to Sitora API for transcription
     */
    protected function sendToSitoraApi($job, $task)
    {
        $apiBaseUrl = SystemSetting::getValue('sitora_api_url', 'http://localhost:5000');

        $audioFullPath = Yii::getAlias('@frontend/web') . $job->audio_path;

        if (!file_exists($audioFullPath)) {
            Yii::error("Audio file not found: {$audioFullPath}");
            $task->markAsFailed('Audio file not found', 'FILE_ERROR');
            return;
        }

        try {
            $cFile = new \CURLFile($audioFullPath, 'audio/' . $job->audio_format, $job->audio_filename);

            $postData = [
                'audio' => $cFile,
                'job_id' => $job->id,
                'task_id' => $task->id,
                'language' => 'uz',
            ];

            $ch = curl_init($apiBaseUrl . '/transcribe');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new \Exception("cURL error: {$curlError}");
            }

            if ($httpCode !== 200) {
                throw new \Exception("Sitora API returned HTTP {$httpCode}: {$response}");
            }

            $responseData = json_decode($response, true);

            if (!isset($responseData['task_id'])) {
                throw new \Exception("Invalid response from Sitora API: " . $response);
            }

            $taskId = $responseData['task_id'];
            $task->external_task_id = $taskId;
            $task->markAsProcessing($taskId, 'sitora-laptop2');
            $job->markAsProcessing();

            Yii::info("Sent job #{$job->id} to Sitora API, task_id: {$taskId}");

        } catch (\Exception $e) {
            Yii::error("Failed to send to Sitora API: " . $e->getMessage());
            $task->markAsFailed('Failed to connect to Sitora API: ' . $e->getMessage(), 'API_ERROR');
        }
    }

    /**
     * Check transcription status from Sitora API
     */
    protected function checkSitoraApiStatus($task)
    {
        $apiBaseUrl = SystemSetting::getValue('sitora_api_url', 'http://localhost:5000');

        if (!$task->external_task_id) {
            return;
        }

        try {
            $taskId = $task->external_task_id;

            $ch = curl_init($apiBaseUrl . '/status/' . $taskId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Yii::warning("Sitora API status check failed: HTTP {$httpCode}");
                return;
            }

            $responseData = json_decode($response, true);

            if (isset($responseData['status']) && $responseData['status'] === 'completed') {
                if (isset($responseData['text'])) {
                    $task->markAsCompleted($responseData['text']);
                    Yii::info("Task #{$task->id} completed via Sitora API");
                }
            } elseif (isset($responseData['status']) && $responseData['status'] === 'failed') {
                $errorMessage = $responseData['error'] ?? 'Unknown error';
                $task->markAsFailed($errorMessage, 'SITORA_API_FAILED');
            }

        } catch (\Exception $e) {
            Yii::error("Error checking Sitora API status: " . $e->getMessage());
        }
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    /**
     * Validate uploaded audio file
     */
    protected function validateAudioFile($file)
    {
        $allowedFormats = SystemSetting::getAllowedAudioFormats();
        if (!in_array(strtolower($file->extension), $allowedFormats)) {
            return [
                'valid' => false,
                'error' => 'Noto\'g\'ri format. Ruxsat etilgan: ' . implode(', ', $allowedFormats),
            ];
        }

        $maxSizeMb = SystemSetting::getMaxAudioSize();
        $fileSizeMb = $file->size / 1048576;

        if ($fileSizeMb > $maxSizeMb) {
            return [
                'valid' => false,
                'error' => "Fayl juda katta. Maksimal: {$maxSizeMb} MB",
            ];
        }

        return ['valid' => true];
    }

    /**
     * Get audio duration in seconds
     */
    protected function getAudioDuration($filePath)
    {
        if (class_exists('\getID3')) {
            try {
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze($filePath);

                if (isset($fileInfo['playtime_seconds'])) {
                    return round($fileInfo['playtime_seconds'], 2);
                }
            } catch (\Exception $e) {
                Yii::error("getID3 error: " . $e->getMessage());
            }
        }

        $fileSizeMb = filesize($filePath) / 1048576;
        return round($fileSizeMb * 60, 2);
    }

    /**
     * Save audio file to permanent location
     */
    protected function saveAudioFile($file, $audioId)
    {
        $date = date('Y/m/d');
        $uploadPath = Yii::getAlias('@frontend/web/uploads/' . $date);

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = $audioId . '.' . $file->extension;
        $fullPath = $uploadPath . '/' . $fileName;

        if ($file->saveAs($fullPath)) {
            return '/uploads/' . $date . '/' . $fileName;
        }

        return false;
    }
}