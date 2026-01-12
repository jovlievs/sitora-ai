<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Task model - Tracks Python API processing
 *
 * @property integer $id
 * @property integer $job_id
 * @property string $external_task_id
 * @property string $status
 * @property integer $attempt_number
 * @property integer $max_attempts
 * @property string $worker_host
 * @property string $processing_started_at
 * @property string $processing_completed_at
 * @property integer $processing_duration_seconds
 * @property string $result_text
 * @property string $error_message
 * @property string $error_code
 * @property integer $priority
 * @property string $reserved_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property TranscriptionJob $job
 */
class Task extends ActiveRecord
{
    const STATUS_QUEUED = 'queued';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_TIMEOUT = 'timeout';

    const PRIORITY_HIGHEST = 1;
    const PRIORITY_HIGH = 3;
    const PRIORITY_NORMAL = 5;
    const PRIORITY_LOW = 7;
    const PRIORITY_LOWEST = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%task}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_id'], 'required'],
            
            ['job_id', 'integer'],
            ['job_id', 'exist', 'skipOnError' => true, 'targetClass' => TranscriptionJob::class, 'targetAttribute' => ['job_id' => 'id']],
            
            ['external_task_id', 'string', 'max' => 100],
            ['external_task_id', 'unique'],
            
            ['status', 'string'],
            ['status', 'in', 'range' => [
                self::STATUS_QUEUED,
                self::STATUS_PROCESSING,
                self::STATUS_COMPLETED,
                self::STATUS_FAILED,
                self::STATUS_TIMEOUT
            ]],
            ['status', 'default', 'value' => self::STATUS_QUEUED],
            
            ['attempt_number', 'integer', 'min' => 1],
            ['attempt_number', 'default', 'value' => 1],
            
            ['max_attempts', 'integer', 'min' => 1, 'max' => 10],
            ['max_attempts', 'default', 'value' => 3],
            
            ['worker_host', 'string', 'max' => 100],
            
            ['processing_started_at', 'safe'],
            ['processing_completed_at', 'safe'],
            ['processing_duration_seconds', 'integer', 'min' => 0],
            
            ['result_text', 'string'],
            ['error_message', 'string'],
            ['error_code', 'string', 'max' => 50],
            
            ['priority', 'integer', 'min' => 1, 'max' => 10],
            ['priority', 'default', 'value' => self::PRIORITY_NORMAL],
            
            ['reserved_at', 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_id' => 'Job ID',
            'external_task_id' => 'Python Task ID',
            'status' => 'Status',
            'attempt_number' => 'Attempt #',
            'max_attempts' => 'Max Attempts',
            'worker_host' => 'Worker Host',
            'processing_started_at' => 'Started At',
            'processing_completed_at' => 'Completed At',
            'processing_duration_seconds' => 'Duration (sec)',
            'result_text' => 'Result',
            'error_message' => 'Error',
            'priority' => 'Priority',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[TranscriptionJob]].
     */
    public function getJob()
    {
        return $this->hasOne(TranscriptionJob::class, ['id' => 'job_id']);
    }

    // ========================================================================
    // TASK LIFECYCLE METHODS
    // ========================================================================

    /**
     * Mark task as processing
     * 
     * @param string|null $externalTaskId Task ID from Python API
     * @param string|null $workerHost Which Python server is processing
     * @return bool
     */
    public function markAsProcessing($externalTaskId = null, $workerHost = null)
    {
        $this->status = self::STATUS_PROCESSING;
        $this->external_task_id = $externalTaskId;
        $this->worker_host = $workerHost;
        $this->processing_started_at = date('Y-m-d H:i:s');
        
        return $this->save(false);
    }

    /**
     * Mark task as completed with result
     * 
     * @param string $resultText Transcription text from Python API
     * @return bool
     */
    public function markAsCompleted($resultText)
    {
        $this->status = self::STATUS_COMPLETED;
        $this->result_text = $resultText;
        $this->processing_completed_at = date('Y-m-d H:i:s');
        
        if ($this->processing_started_at) {
            $start = strtotime($this->processing_started_at);
            $end = strtotime($this->processing_completed_at);
            $this->processing_duration_seconds = $end - $start;
        }
        
        $saved = $this->save(false);
        
        // Update parent job
        if ($saved && $this->job) {
            $this->job->markAsCompleted($resultText);
        }
        
        return $saved;
    }

    /**
     * Mark task as failed
     * 
     * @param string $errorMessage
     * @param string|null $errorCode
     * @return bool
     */
    public function markAsFailed($errorMessage, $errorCode = null)
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->error_code = $errorCode;
        $this->processing_completed_at = date('Y-m-d H:i:s');
        
        if ($this->processing_started_at) {
            $start = strtotime($this->processing_started_at);
            $end = strtotime($this->processing_completed_at);
            $this->processing_duration_seconds = $end - $start;
        }
        
        $saved = $this->save(false);
        
        // Check if we should retry or mark job as failed
        if ($saved) {
            if ($this->canRetry()) {
                $this->createRetryTask();
            } else {
                // No more retries, mark job as failed
                if ($this->job) {
                    $this->job->markAsFailed($errorMessage);
                }
            }
        }
        
        return $saved;
    }

    /**
     * Mark task as timeout
     * 
     * @return bool
     */
    public function markAsTimeout()
    {
        $this->status = self::STATUS_TIMEOUT;
        $this->error_message = 'Task exceeded maximum processing time';
        $this->processing_completed_at = date('Y-m-d H:i:s');
        
        $saved = $this->save(false);
        
        // Retry logic same as failed
        if ($saved && $this->canRetry()) {
            $this->createRetryTask();
        } else if ($saved && $this->job) {
            $this->job->markAsFailed('Task timeout after ' . $this->attempt_number . ' attempts');
        }
        
        return $saved;
    }

    /**
     * Check if task can be retried
     * 
     * @return bool
     */
    public function canRetry()
    {
        return $this->attempt_number < $this->max_attempts;
    }

    /**
     * Create a retry task
     * 
     * @return Task|null
     */
    public function createRetryTask()
    {
        $newTask = new Task([
            'job_id' => $this->job_id,
            'attempt_number' => $this->attempt_number + 1,
            'max_attempts' => $this->max_attempts,
            'priority' => $this->priority,
            'status' => self::STATUS_QUEUED,
        ]);
        
        if ($newTask->save()) {
            Yii::info("Created retry task #{$newTask->id} for job #{$this->job_id}, attempt {$newTask->attempt_number}");
            return $newTask;
        }
        
        return null;
    }

    /**
     * Find tasks ready for processing (queue worker)
     * 
     * @param int $limit
     * @return Task[]
     */
    public static function findPendingTasks($limit = 10)
    {
        return self::find()
            ->where(['status' => self::STATUS_QUEUED])
            ->orderBy(['priority' => SORT_ASC, 'created_at' => SORT_ASC])
            ->limit($limit)
            ->all();
    }

    /**
     * Reserve task for processing (Faza 2 - with queue)
     * 
     * @return bool
     */
    public function reserve()
    {
        $this->reserved_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * Get tasks that are stuck (processing too long)
     * 
     * @param int $timeoutMinutes Default 30 minutes
     * @return Task[]
     */
    public static function findStuckTasks($timeoutMinutes = 30)
    {
        $cutoffTime = date('Y-m-d H:i:s', time() - ($timeoutMinutes * 60));
        
        return self::find()
            ->where(['status' => self::STATUS_PROCESSING])
            ->andWhere(['<', 'processing_started_at', $cutoffTime])
            ->all();
    }

    /**
     * Get formatted status for display
     * 
     * @return string
     */
    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_QUEUED => 'Navbatda',
            self::STATUS_PROCESSING => 'Ishlanmoqda',
            self::STATUS_COMPLETED => 'Tayyor',
            self::STATUS_FAILED => 'Xatolik',
            self::STATUS_TIMEOUT => 'Vaqt tugadi',
        ];
        
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status color for UI
     * 
     * @return string
     */
    public function getStatusColor()
    {
        $colors = [
            self::STATUS_QUEUED => 'secondary',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_TIMEOUT => 'warning',
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }
}
