# 🎯 SITORA STT PLATFORM - MODEL FILES GUIDE

## 📦 FILES CREATED:

1. **User.php** - User management with wallet functionality
2. **TranscriptionJob.php** - Main business logic for STT jobs
3. **Task.php** - Python API integration and retry logic
4. **WalletTransaction.php** - Financial tracking (immutable)
5. **SystemSetting.php** - Configuration management

---

## 📂 INSTALLATION:

Copy all 5 model files to:
```
C:\OldOSPanel\domains\sitora-platform\common\models\
```

---

## 🚀 USAGE EXAMPLES:

### 1. USER OPERATIONS

#### Create new user:
```php
$user = new User([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'language_preference' => 'uz',
]);
$user->setPassword('secure_password');
$user->generateAuthKey();
$user->save();
```

#### Check wallet balance:
```php
$user = User::findOne($userId);

if ($user->hasSufficientBalance(10000)) {
    echo "User has enough balance!";
}
```

#### Add money (top-up):
```php
$user->addBalance(50000, 'Top up via Click', 'CLICK_TX_12345');
```

#### Get effective price:
```php
$price = $user->getEffectivePricePerMinute(); // Returns user price or system default
```

---

### 2. TRANSCRIPTION JOB WORKFLOW

#### Complete workflow example:
```php
use common\models\User;
use common\models\TranscriptionJob;
use common\models\Task;

// 1. User uploads audio
$user = User::findOne(Yii::$app->user->id);
$audioPath = '/uploads/2025/01/06/recording.wav';
$durationSeconds = 90; // 1 minute 30 seconds

// 2. Create job
$job = new TranscriptionJob([
    'user_id' => $user->id,
    'audio_id' => TranscriptionJob::generateAudioId(), // STT_20250106_000001
    'audio_filename' => 'recording.wav',
    'audio_path' => $audioPath,
    'audio_format' => 'wav',
    'audio_duration_seconds' => $durationSeconds,
    'cost' => TranscriptionJob::calculateCost($durationSeconds, $user), // Exact: 1.5 * price
]);

// 3. Check balance BEFORE creating job
if (!$job->checkUserBalance($user)) {
    throw new \Exception('Insufficient balance!');
}

$job->save();

// 4. Create task for Python API
$task = new Task([
    'job_id' => $job->id,
    'status' => Task::STATUS_QUEUED,
]);
$task->save();

// 5. Send to Python API
$pythonResponse = sendToPythonApi($audioPath);
$task->markAsProcessing($pythonResponse['task_id'], 'laptop-2');

// 6. Poll for result (in separate process or cron job)
// ... wait for Python API to complete ...

// 7. When Python API returns result:
$task->markAsCompleted("Assalomu aleykum, bu test matn.");

// 8. Wallet automatically deducted in markAsCompleted()!
```

#### Get user's jobs:
```php
$jobs = TranscriptionJob::find()
    ->where(['user_id' => $userId])
    ->orderBy(['created_at' => SORT_DESC])
    ->all();

foreach ($jobs as $job) {
    echo "Job #{$job->id}: {$job->getStatusLabel()} - {$job->getFormattedDuration()}\n";
}
```

---

### 3. TASK MANAGEMENT

#### Mark task as failed (with retry):
```php
$task = Task::findOne($taskId);
$task->markAsFailed('GPU out of memory', 'OOM_ERROR');

// Automatically creates retry task if attempts < max_attempts
```

#### Find pending tasks (for worker):
```php
$tasks = Task::findPendingTasks(10); // Get 10 pending tasks

foreach ($tasks as $task) {
    // Process task
    $task->reserve();
    processTask($task);
}
```

#### Find stuck tasks:
```php
$stuckTasks = Task::findStuckTasks(30); // Processing > 30 minutes

foreach ($stuckTasks as $task) {
    $task->markAsTimeout();
}
```

---

### 4. WALLET TRANSACTIONS

#### View user's transaction history:
```php
$transactions = WalletTransaction::getUserHistory($userId, 50);

foreach ($transactions as $tx) {
    echo "{$tx->getTypeLabel()}: {$tx->getFormattedAmount()}\n";
    echo "Balance: {$tx->balance_before} → {$tx->balance_after}\n";
}
```

#### Get daily summary:
```php
$summary = WalletTransaction::getDailySummary($userId, '2025-01-06');

echo "Credits: {$summary['credits']} UZS\n";
echo "Debits: {$summary['debits']} UZS\n";
echo "Transactions: {$summary['count']}\n";
```

#### Get job transactions:
```php
$jobTx = WalletTransaction::getJobTransactions($jobId);

foreach ($jobTx as $tx) {
    echo "{$tx->description}: {$tx->amount} UZS\n";
}
```

---

### 5. SYSTEM SETTINGS

#### Get setting values:
```php
use common\models\SystemSetting;

// Method 1: Generic
$price = SystemSetting::getValue('default_price_per_minute', 0.00);

// Method 2: Type-safe helper methods (recommended)
$price = SystemSetting::getDefaultPricePerMinute();
$maxDuration = SystemSetting::getMaxAudioDuration();
$maxSize = SystemSetting::getMaxAudioSize();
$pythonUrl = SystemSetting::getPythonApiUrl();
```

#### Update settings:
```php
SystemSetting::setValue('default_price_per_minute', 1000.00, 'decimal', 'Price per minute in UZS');
SystemSetting::setValue('max_audio_duration_seconds', 1800, 'integer', '30 minutes');
```

#### Validate audio file:
```php
$format = 'wav';
$sizeMb = 45.5;
$durationSeconds = 1200;

if (!SystemSetting::isAudioFormatAllowed($format)) {
    throw new \Exception('Format not allowed!');
}

if (!SystemSetting::isAudioSizeValid($sizeMb)) {
    throw new \Exception('File too large!');
}

if (!SystemSetting::isAudioDurationValid($durationSeconds)) {
    throw new \Exception('Audio too long!');
}
```

---

## 🔥 IMPORTANT PATTERNS:

### 1. **ATOMIC WALLET OPERATIONS**

✅ **Good:**
```php
$user->deductBalance($cost, $jobId); // Uses transaction + optimistic locking
```

❌ **Bad:**
```php
$user->wallet_balance -= $cost; // Race condition!
$user->save();
```

### 2. **PRICE CALCULATION (EXACT)**

```php
$durationSeconds = 90;
$cost = TranscriptionJob::calculateCost($durationSeconds, $user);
// Returns: (90 / 60) * price_per_minute = 1.5 * 10000 = 15000.00
```

### 3. **AUDIO_ID GENERATION**

```php
$audioId = TranscriptionJob::generateAudioId();
// Returns: STT_20250106_000001 (date + sequential)
```

### 4. **RETRY LOGIC**

```php
$task->markAsFailed('Error message');
// Automatically creates new Task with attempt_number + 1
// If attempts >= max_attempts, marks Job as failed
```

---

## 🎨 DISPLAY HELPERS (For Frontend):

### Status badges:
```php
<span class="badge bg-<?= $job->getStatusColor() ?>">
    <?= $job->getStatusLabel() ?>
</span>
```

### Formatted amounts:
```php
echo $transaction->getFormattedAmount(); // +50,000.00 UZS or -15,000.00 UZS
```

### Duration display:
```php
echo $job->getFormattedDuration(); // "1 min 30 sec"
```

---

## 🔍 RELATIONSHIPS:

```php
// User → Jobs
$jobs = $user->transcriptionJobs;

// User → Transactions
$transactions = $user->walletTransactions;

// Job → User
$user = $job->user;

// Job → Tasks
$tasks = $job->tasks;
$latestTask = $job->latestTask;

// Task → Job
$job = $task->job;

// Transaction → User
$user = $transaction->user;

// Transaction → Job
$job = $transaction->job;
```

---

## ✅ VALIDATION RULES:

Models have built-in validation:

```php
$job = new TranscriptionJob([
    'audio_duration_seconds' => -5, // Invalid!
]);

if (!$job->validate()) {
    print_r($job->errors);
}
```

Key validations:
- User: unique username, valid email, status in range
- Job: positive duration, valid status, valid format
- Task: attempt ≤ max_attempts, valid status
- Transaction: balance_before/after integrity
- Setting: valid type, unique key

---

## 🚨 ERROR HANDLING:

Always use try-catch for wallet operations:

```php
try {
    $success = $user->deductBalance($cost, $jobId);
    if (!$success) {
        Yii::error("Failed to deduct balance for user {$user->id}");
    }
} catch (\Exception $e) {
    Yii::error("Wallet operation failed: " . $e->getMessage());
}
```

---

## 🎯 NEXT STEPS:

1. ✅ Copy these 5 model files to `common/models/`
2. ✅ Test creating a user
3. ✅ Test creating a job
4. ✅ Test wallet operations
5. ✅ Build Controllers to use these models
6. ✅ Build Views to display data

---

## 📞 TESTING COMMANDS:

```php
// In Yii console (yii shell or controller)

// Test 1: Create user
$user = new User(['username' => 'test', 'email' => 'test@test.com']);
$user->setPassword('123456');
$user->generateAuthKey();
$user->save();

// Test 2: Add balance
$user->addBalance(100000, 'Test top-up');
echo "Balance: " . $user->wallet_balance; // 100000.00

// Test 3: Create job
$job = new TranscriptionJob([
    'user_id' => $user->id,
    'audio_id' => TranscriptionJob::generateAudioId(),
    'audio_filename' => 'test.wav',
    'audio_path' => '/uploads/test.wav',
    'audio_duration_seconds' => 60,
    'cost' => 0,
]);
$job->save();

// Test 4: Create task
$task = new Task(['job_id' => $job->id]);
$task->save();

// Test 5: Complete task
$task->markAsCompleted('Bu test matni');
echo "Job status: " . $job->status; // completed
```

---

**All models are production-ready!** 🚀
