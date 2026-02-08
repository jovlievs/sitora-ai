<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * TranscriptionJob model
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $audio_id
 * @property string $audio_filename
 * @property string $audio_path
 * @property string $audio_format
 * @property string $language
 * @property float $audio_duration_seconds
 * @property float $audio_size_mb
 * @property integer $audio_sample_rate
 * @property float $cost
 * @property string $status
 * @property string $transcription_text
 * @property float $confidence_score
 * @property integer $word_count
 * @property string $ip_address
 * @property string $user_agent
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $completed_at
 *
 * @property User $user
 * @property Task[] $tasks
 */
class TranscriptionJob extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%transcription_job}}';
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
            // Required fields (removed user_id!)
            [['audio_id', 'audio_filename', 'audio_path', 'audio_duration_seconds', 'cost'], 'required'],

            // Allow NULL user_id for guest uploads
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            ['user_id', 'exist', 'skipOnError' => true, 'skipOnEmpty' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],

            ['audio_id', 'string', 'max' => 50],
            ['audio_id', 'unique'],


            ['audio_filename', 'string', 'max' => 255],
            ['audio_path', 'string', 'max' => 500],

            ['audio_format', 'string', 'max' => 10],
            ['audio_format', 'in', 'range' => ['wav', 'mp3', 'ogg']],
            ['audio_format', 'default', 'value' => 'wav'],

            ['language', 'string', 'max' => 10],
            ['language', 'default', 'value' => 'uz'],

            ['audio_duration_seconds', 'number', 'min' => 0.01],
            ['audio_size_mb', 'number', 'min' => 0],
            ['audio_sample_rate', 'integer'],

            ['cost', 'number', 'min' => 0],

            ['status', 'string'],
            ['status', 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_PROCESSING,
                self::STATUS_COMPLETED,
                self::STATUS_FAILED,
                self::STATUS_CANCELLED
            ]],
            ['status', 'default', 'value' => self::STATUS_PENDING],

            ['transcription_text', 'string'],
            ['confidence_score', 'number', 'min' => 0, 'max' => 1],
            ['word_count', 'integer', 'min' => 0],

            ['ip_address', 'string', 'max' => 45],
            ['user_agent', 'string', 'max' => 500],

            ['completed_at', 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'audio_id' => 'Audio ID',
            'audio_filename' => 'Original Filename',
            'audio_path' => 'File Path',
            'audio_format' => 'Format',
            'language' => 'Language',
            'audio_duration_seconds' => 'Duration (seconds)',
            'audio_size_mb' => 'Size (MB)',
            'audio_sample_rate' => 'Sample Rate',
            'cost' => 'Cost (UZS)',
            'status' => 'Status',
            'transcription_text' => 'Transcription',
            'confidence_score' => 'Confidence',
            'word_count' => 'Word Count',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'completed_at' => 'Completed At',
        ];
    }

    /**
     * Gets query for [[User]].
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Task]].
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['job_id' => 'id'])->orderBy(['attempt_number' => SORT_ASC]);
    }

    /**
     * Get the latest task
     */
    public function getLatestTask()
    {
        return $this->hasOne(Task::class, ['job_id' => 'id'])->orderBy(['attempt_number' => SORT_DESC]);
    }

    // ========================================================================
    // BUSINESS LOGIC METHODS
    // ========================================================================

    /**
     * Generate unique audio_id
     * Format: STT_YYYYMMDD_NNNNNN
     * 
     * @return string
     */
    public static function generateAudioId()
    {
        $date = date('Ymd');
        
        // Get today's count
        $count = self::find()
            ->where(['like', 'audio_id', "STT_{$date}_%", false])
            ->count();
        
        $sequential = str_pad($count + 1, 6, '0', STR_PAD_LEFT);
        
        return "STT_{$date}_{$sequential}";
    }

    /**
     * Calculate cost based on duration and user's price
     * 
     * @param float $durationSeconds
     * @param User $user
     * @return float
     */
    public static function calculateCost($durationSeconds, $user)
    {
        $durationMinutes = $durationSeconds / 60; // Exact calculation
        $pricePerMinute = $user->getEffectivePricePerMinute();
        
        return round($durationMinutes * $pricePerMinute, 2);
    }

    /**
     * Mark job as processing
     */
    public function markAsProcessing()
    {
        $this->status = self::STATUS_PROCESSING;
        return $this->save(false);
    }

    /**
     * Mark job as completed with transcription result
     * 
     * @param string $transcriptionText
     * @param float|null $confidenceScore
     * @return bool
     */
    public function markAsCompleted($transcriptionText, $confidenceScore = null)
    {
        $this->status = self::STATUS_COMPLETED;
        $this->transcription_text = $transcriptionText;
        $this->confidence_score = $confidenceScore;
        $this->word_count = str_word_count($transcriptionText);
        $this->completed_at = time();
        
        $saved = $this->save(false);
        
        // Deduct wallet after successful completion
        if ($saved && $this->cost > 0) {
            $this->user->deductBalance($this->cost, $this->id, "Transcription: {$this->audio_id}");
        }
        
        return $saved;
    }

    /**
     * Mark job as failed
     * 
     * @param string|null $errorMessage
     * @return bool
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->status = self::STATUS_FAILED;
        
        // No wallet deduction for failed jobs
        // Could add refund logic here if needed
        
        return $this->save(false);
    }

    /**
     * Cancel job (user requested)
     * 
     * @return bool
     */
    public function cancel()
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save(false);
    }

    /**
     * Check if job can be cancelled
     * 
     * @return bool
     */
    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Get human-readable duration
     * 
     * @return string
     */
    public function getFormattedDuration()
    {
        $seconds = (int) $this->audio_duration_seconds;
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($minutes > 0) {
            return sprintf('%d min %d sec', $minutes, $remainingSeconds);
        }
        
        return sprintf('%d sec', $seconds);
    }

    /**
     * Get status badge color for UI
     * 
     * @return string
     */
    public function getStatusColor()
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get status label in Uzbek
     * 
     * @return string
     */
    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_PENDING => 'Kutilmoqda',
            self::STATUS_PROCESSING => 'Ishlanmoqda',
            self::STATUS_COMPLETED => 'Tayyor',
            self::STATUS_FAILED => 'Xatolik',
            self::STATUS_CANCELLED => 'Bekor qilindi',
        ];
        
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Check if user has sufficient balance before creating job
     * 
     * @param User $user
     * @return bool
     */
    public function checkUserBalance($user)
    {
        return $user->hasSufficientBalance($this->cost);
    }

    /**
     * Before save: set IP and User Agent
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->ip_address = Yii::$app->request->userIP ?? null;
                $this->user_agent = Yii::$app->request->userAgent ?? null;
            }
            return true;
        }
        return false;
    }
    public function ensureAudioToken(): void {
        if(empty($this->audio_token)){
            $this->audio_token = bin2hex(random_bytes(16));
        }
    }
}
