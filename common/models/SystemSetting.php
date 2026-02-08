<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * SystemSetting model
 *
 * @property string $setting_key
 * @property string $setting_value
 * @property string $setting_type
 * @property string $description
 * @property integer $is_public
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property User $updatedBy
 */
class SystemSetting extends ActiveRecord
{
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%system_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['setting_key', 'setting_value'], 'required'],
            
            ['setting_key', 'string', 'max' => 100],
            ['setting_key', 'unique'],
            
            ['setting_value', 'string'],
            
            ['setting_type', 'string'],
            ['setting_type', 'in', 'range' => [
                self::TYPE_STRING,
                self::TYPE_INTEGER,
                self::TYPE_DECIMAL,
                self::TYPE_BOOLEAN,
                self::TYPE_JSON
            ]],
            ['setting_type', 'default', 'value' => self::TYPE_STRING],
            
            ['description', 'string'],
            
            ['is_public', 'boolean'],
            ['is_public', 'default', 'value' => 0],
            
            ['updated_at', 'integer'],
            ['updated_by', 'integer'],
            ['updated_by', 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'setting_key' => 'Key',
            'setting_value' => 'Value',
            'setting_type' => 'Type',
            'description' => 'Description',
            'is_public' => 'Public',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[User]].
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Before save: update timestamp
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->updated_at = time();
            
            // Set updated_by if user is logged in
            if (!Yii::$app->user->isGuest) {
                $this->updated_by = Yii::$app->user->id;
            }
            
            return true;
        }
        return false;
    }

    // ========================================================================
    // STATIC HELPER METHODS (Most Important!)
    // ========================================================================

    /**
     * Get setting value by key
     * 
     * @param string $key
     * @param mixed $default Default value if setting not found
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::findOne($key);
        
        if (!$setting) {
            return $default;
        }
        
        return self::castValue($setting->setting_value, $setting->setting_type);
    }

    /**
     * Set setting value by key
     * 
     * @param string $key
     * @param mixed $value
     * @param string|null $type
     * @param string|null $description
     * @return bool
     */
    public static function setValue($key, $value, $type = null, $description = null)
    {
        $setting = self::findOne($key);
        
        if (!$setting) {
            $setting = new self([
                'setting_key' => $key,
                'setting_type' => $type ?? self::TYPE_STRING,
                'description' => $description,
            ]);
        }
        
        // Convert value to string for storage
        if ($setting->setting_type === self::TYPE_JSON && is_array($value)) {
                $setting->setting_value = json_encode($value);
        } elseif ($setting->setting_type === self::TYPE_BOOLEAN) {
            $setting->setting_value = $value ? '1' : '0';
        } else {
            $setting->setting_value = (string) $value;
        }
        
        return $setting->save();
    }

    /**
     * Cast string value to appropriate type
     * 
     * @param string $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue($value, $type)
    {
        switch ($type) {
            case self::TYPE_INTEGER:
                return (int) $value;
                
            case self::TYPE_DECIMAL:
                return (float) $value;
                
            case self::TYPE_BOOLEAN:
                return (bool) $value || $value === '1' || $value === 'true';
                
            case self::TYPE_JSON:
                return json_decode($value, true);
                
            case self::TYPE_STRING:
            default:
                return $value;
        }
    }

    /**
     * Get all public settings (for frontend display)
     * 
     * @return array Key-value pairs
     */
    public static function getPublicSettings()
    {
        $settings = self::find()
            ->where(['is_public' => 1])
            ->all();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->setting_key] = self::castValue(
                $setting->setting_value, 
                $setting->setting_type
            );
        }
        
        return $result;
    }

    /**
     * Get all settings as key-value array
     * 
     * @param bool $publicOnly
     * @return array
     */
    public static function getAllSettings($publicOnly = false)
    {
        $query = self::find();
        
        if ($publicOnly) {
            $query->where(['is_public' => 1]);
        }
        
        $settings = $query->all();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->setting_key] = self::castValue(
                $setting->setting_value,
                $setting->setting_type
            );
        }
        
        return $result;
    }

    // ========================================================================
    // PREDEFINED SETTING KEYS (for type safety)
    // ========================================================================

    /**
     * Get default price per minute
     * 
     * @return float
     */
    public static function getDefaultPricePerMinute()
    {
        return (float) self::getValue('default_price_per_minute', 0.00);
    }

    /**
     * Get max audio duration in seconds
     * 
     * @return int
     */
    public static function getMaxAudioDuration()
    {
        return (int) self::getValue('max_audio_duration_seconds', 1800);
    }

    /**
     * Get max audio file size in MB
     * 
     * @return int
     */
    public static function getMaxAudioSize()
    {
        return (int) self::getValue('max_audio_size_mb', 100);
    }

    /**
     * Get allowed audio formats
     * 
     * @return array
     */
    public static function getAllowedAudioFormats()
    {
        $formats = self::getValue('allowed_audio_formats', 'wav,mp3,ogg');
        return explode(',', $formats);
    }

    /**
     * Get Python API URL
     * 
     * @return string
     */
    public static function getPythonApiUrl()
    {
        return self::getValue('python_api_url', 'http://localhost:5000');
    }

    /**
     * Get task polling interval in seconds
     * 
     * @return int
     */
    public static function getTaskPollingInterval()
    {
        return (int) self::getValue('task_polling_interval_seconds', 5);
    }

    /**
     * Get max task retry attempts
     * 
     * @return int
     */
    public static function getMaxTaskAttempts()
    {
        return (int) self::getValue('max_task_attempts', 3);
    }

    /**
     * Check if audio format is allowed
     * 
     * @param string $format
     * @return bool
     */
    public static function isAudioFormatAllowed($format)
    {
        $allowed = self::getAllowedAudioFormats();
        return in_array(strtolower($format), array_map('strtolower', $allowed));
    }

    /**
     * Validate audio file size
     * 
     * @param float $sizeMb
     * @return bool
     */
    public static function isAudioSizeValid($sizeMb)
    {
        return $sizeMb <= self::getMaxAudioSize();
    }

    /**
     * Validate audio duration
     * 
     * @param float $durationSeconds
     * @return bool
     */
    public static function isAudioDurationValid($durationSeconds)
    {
        return $durationSeconds <= self::getMaxAudioDuration();
    }
}
