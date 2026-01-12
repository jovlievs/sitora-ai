<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * WalletTransaction model
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $job_id
 * @property float $amount
 * @property string $transaction_type
 * @property float $balance_before
 * @property float $balance_after
 * @property string $description
 * @property string $reference_id
 * @property string $admin_note
 * @property integer $created_at
 *
 * @property User $user
 * @property TranscriptionJob $job
 */
class WalletTransaction extends ActiveRecord
{
    const TYPE_TOP_UP = 'top_up';
    const TYPE_DEDUCTION = 'deduction';
    const TYPE_REFUND = 'refund';
    const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wallet_transaction}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false, // No updated_at for transactions
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'amount', 'transaction_type', 'balance_before', 'balance_after'], 'required'],
            
            ['user_id', 'integer'],
            ['user_id', 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            
            ['job_id', 'integer'],
            ['job_id', 'exist', 'skipOnError' => true, 'targetClass' => TranscriptionJob::class, 'targetAttribute' => ['job_id' => 'id']],
            
            ['amount', 'number'],
            
            ['transaction_type', 'string'],
            ['transaction_type', 'in', 'range' => [
                self::TYPE_TOP_UP,
                self::TYPE_DEDUCTION,
                self::TYPE_REFUND,
                self::TYPE_ADJUSTMENT
            ]],
            
            ['balance_before', 'number', 'min' => 0],
            ['balance_after', 'number', 'min' => 0],
            
            ['description', 'string', 'max' => 255],
            ['reference_id', 'string', 'max' => 100],
            ['admin_note', 'string'],
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
            'job_id' => 'Job ID',
            'amount' => 'Amount (UZS)',
            'transaction_type' => 'Type',
            'balance_before' => 'Balance Before',
            'balance_after' => 'Balance After',
            'description' => 'Description',
            'reference_id' => 'Reference ID',
            'admin_note' => 'Admin Note',
            'created_at' => 'Date',
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
     * Gets query for [[TranscriptionJob]].
     */
    public function getJob()
    {
        return $this->hasOne(TranscriptionJob::class, ['id' => 'job_id']);
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    /**
     * Get formatted amount with sign
     * 
     * @return string
     */
    public function getFormattedAmount()
    {
        $sign = $this->amount >= 0 ? '+' : '';
        return $sign . number_format($this->amount, 2, '.', ',') . ' UZS';
    }

    /**
     * Get transaction type label in Uzbek
     * 
     * @return string
     */
    public function getTypeLabel()
    {
        $labels = [
            self::TYPE_TOP_UP => 'To\'ldirish',
            self::TYPE_DEDUCTION => 'Yechib olish',
            self::TYPE_REFUND => 'Qaytarish',
            self::TYPE_ADJUSTMENT => 'Tuzatish',
        ];
        
        return $labels[$this->transaction_type] ?? $this->transaction_type;
    }

    /**
     * Get transaction type color for UI
     * 
     * @return string
     */
    public function getTypeColor()
    {
        $colors = [
            self::TYPE_TOP_UP => 'success',
            self::TYPE_DEDUCTION => 'danger',
            self::TYPE_REFUND => 'info',
            self::TYPE_ADJUSTMENT => 'warning',
        ];
        
        return $colors[$this->transaction_type] ?? 'secondary';
    }

    /**
     * Check if transaction is credit (adds money)
     * 
     * @return bool
     */
    public function isCredit()
    {
        return $this->amount > 0;
    }

    /**
     * Check if transaction is debit (removes money)
     * 
     * @return bool
     */
    public function isDebit()
    {
        return $this->amount < 0;
    }

    /**
     * Get user's transaction history
     * 
     * @param int $userId
     * @param int $limit
     * @return WalletTransaction[]
     */
    public static function getUserHistory($userId, $limit = 50)
    {
        return self::find()
            ->where(['user_id' => $userId])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Get total credited amount for user
     * 
     * @param int $userId
     * @return float
     */
    public static function getTotalCredits($userId)
    {
        return (float) self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['>', 'amount', 0])
            ->sum('amount') ?? 0;
    }

    /**
     * Get total debited amount for user
     * 
     * @param int $userId
     * @return float
     */
    public static function getTotalDebits($userId)
    {
        return abs((float) self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['<', 'amount', 0])
            ->sum('amount') ?? 0);
    }

    /**
     * Get transactions for a specific job
     * 
     * @param int $jobId
     * @return WalletTransaction[]
     */
    public static function getJobTransactions($jobId)
    {
        return self::find()
            ->where(['job_id' => $jobId])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
    }

    /**
     * Verify transaction balance integrity
     * 
     * @return bool
     */
    public function verifyBalance()
    {
        if ($this->amount >= 0) {
            // Credit: balance_after should be balance_before + amount
            return abs($this->balance_after - ($this->balance_before + $this->amount)) < 0.01;
        } else {
            // Debit: balance_after should be balance_before + amount (amount is negative)
            return abs($this->balance_after - ($this->balance_before + $this->amount)) < 0.01;
        }
    }

    /**
     * Get daily transaction summary for user
     * 
     * @param int $userId
     * @param string $date Format: Y-m-d
     * @return array ['credits' => float, 'debits' => float, 'count' => int]
     */
    public static function getDailySummary($userId, $date)
    {
        $startOfDay = strtotime($date . ' 00:00:00');
        $endOfDay = strtotime($date . ' 23:59:59');
        
        $transactions = self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['>=', 'created_at', $startOfDay])
            ->andWhere(['<=', 'created_at', $endOfDay])
            ->all();
        
        $credits = 0;
        $debits = 0;
        
        foreach ($transactions as $tx) {
            if ($tx->amount > 0) {
                $credits += $tx->amount;
            } else {
                $debits += abs($tx->amount);
            }
        }
        
        return [
            'credits' => $credits,
            'debits' => $debits,
            'count' => count($transactions),
        ];
    }

    /**
     * Prevent modification of transactions (immutable)
     */
    public function beforeSave($insert)
    {
        if (!$insert) {
            // Transactions should not be updated, only inserted
            throw new \yii\base\InvalidCallException('Wallet transactions cannot be modified after creation');
        }
        
        return parent::beforeSave($insert);
    }
}
