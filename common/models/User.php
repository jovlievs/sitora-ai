<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $auth_key
 * @property string $email
 * @property integer $status
 * @property string $language_preference
 * @property float $wallet_balance
 * @property float $price_per_minute
 * @property string $api_key
 * @property integer $api_enabled
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property TranscriptionJob[] $transcriptionJobs
 * @property WalletTransaction[] $walletTransactions
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
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
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],

            ['username', 'required'],
            ['username', 'string', 'max' => 255],
            ['username', 'unique'],
            ['username', 'trim'],

            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique'],
            ['email', 'trim'],

            ['language_preference', 'string', 'length' => 2],
            ['language_preference', 'in', 'range' => ['uz', 'ru', 'en']],
            ['language_preference', 'default', 'value' => 'uz'],

            ['wallet_balance', 'number', 'min' => 0],
            ['wallet_balance', 'default', 'value' => 0.00],

            ['price_per_minute', 'number', 'min' => 0],
            ['price_per_minute', 'default', 'value' => 0.00],

            ['api_key', 'string', 'max' => 64],
            ['api_key', 'unique'],

            ['api_enabled', 'boolean'],
            ['api_enabled', 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'status' => 'Status',
            'language_preference' => 'Language',
            'wallet_balance' => 'Wallet Balance (UZS)',
            'price_per_minute' => 'Price per Minute',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[TranscriptionJob]].
     */
    public function getTranscriptionJobs()
    {
        return $this->hasMany(TranscriptionJob::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[WalletTransaction]].
     */
    public function getWalletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, ['user_id' => 'id']);
    }

    // ========================================================================
    // IDENTITY INTERFACE METHODS (for Yii2 authentication)
    // ========================================================================

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['api_key' => $token, 'api_enabled' => 1, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates API key
     */
    public function generateApiKey()
    {
        $this->api_key = Yii::$app->security->generateRandomString(64);
    }

    // ========================================================================
    // WALLET METHODS (Critical for STT platform)
    // ========================================================================

    /**
     * Check if user has sufficient balance
     *
     * @param float $amount
     * @return bool
     */
    public function hasSufficientBalance($amount)
    {
        return $this->wallet_balance >= $amount;
    }

    /**
     * Add money to wallet (top-up)
     *
     * @param float $amount
     * @param string $description
     * @param string|null $referenceId Payment gateway transaction ID
     * @return bool
     */
    public function addBalance($amount, $description = 'Top up', $referenceId = null)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Create wallet transaction
            $walletTx = new WalletTransaction([
                'user_id' => $this->id,
                'amount' => $amount,
                'transaction_type' => WalletTransaction::TYPE_TOP_UP,
                'balance_before' => $this->wallet_balance,
                'balance_after' => $this->wallet_balance + $amount,
                'description' => $description,
                'reference_id' => $referenceId,
            ]);

            if (!$walletTx->save()) {
                throw new \Exception('Failed to create wallet transaction');
            }

            // Update user balance with optimistic locking
            $rowsAffected = self::updateAll(
                ['wallet_balance' => $this->wallet_balance + $amount],
                ['id' => $this->id, 'wallet_balance' => $this->wallet_balance]
            );

            if ($rowsAffected === 0) {
                throw new \Exception('Wallet balance was modified by another process');
            }

            $transaction->commit();
            $this->refresh(); // Reload fresh data
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Failed to add balance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deduct money from wallet (after successful transcription)
     *
     * @param float $amount
     * @param int|null $jobId
     * @param string $description
     * @return bool
     */
    public function deductBalance($amount, $jobId = null, $description = 'Transcription service')
    {
        if (!$this->hasSufficientBalance($amount)) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Create wallet transaction
            $walletTx = new WalletTransaction([
                'user_id' => $this->id,
                'job_id' => $jobId,
                'amount' => -$amount, // Negative for deduction
                'transaction_type' => WalletTransaction::TYPE_DEDUCTION,
                'balance_before' => $this->wallet_balance,
                'balance_after' => $this->wallet_balance - $amount,
                'description' => $description,
            ]);

            if (!$walletTx->save()) {
                throw new \Exception('Failed to create wallet transaction');
            }

            // Update user balance with optimistic locking
            $rowsAffected = self::updateAll(
                ['wallet_balance' => $this->wallet_balance - $amount],
                ['id' => $this->id, 'wallet_balance' => $this->wallet_balance]
            );

            if ($rowsAffected === 0) {
                throw new \Exception('Wallet balance was modified by another process');
            }

            $transaction->commit();
            $this->refresh();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Failed to deduct balance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Refund money to wallet (if job failed)
     *
     * @param float $amount
     * @param int|null $jobId
     * @param string $description
     * @return bool
     */
    public function refundBalance($amount, $jobId = null, $description = 'Refund: Job failed')
    {
        return $this->addBalance($amount, $description, null);
    }

    /**
     * Get effective price per minute (user custom or system default)
     *
     * @return float
     */
    public function getEffectivePricePerMinute()
    {
        if ($this->price_per_minute > 0) {
            return $this->price_per_minute;
        }

        return (float) SystemSetting::getValue('default_price_per_minute', 0.00);
    }

    /**
     * Generates email verification token
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
}
