<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\SignupForm */

use yii\helpers\Html;
//use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Ro\'yxatdan o\'tish - Ovoza';

?>


<style>

    :root {
        --dark-bg: #0F0F1E;
        --card-bg: #1A1A2E;
        --primary-purple: #7C3AED;
        --light-purple: #A78BFA;
        --text-light: #E2E8F0;
        --text-muted: #94A3B8;
    }

    body {
        background: var(--dark-bg);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow-x: hidden;
    }

    /* Animated Background */
    body::before {
        content: '';
        position: absolute;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(124, 58, 237, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        top: -300px;
        right: -300px;
        animation: pulse 8s ease-in-out infinite;
    }

    body::after {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(167, 139, 250, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        bottom: -250px;
        left: -250px;
        animation: pulse 6s ease-in-out infinite reverse;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.2); opacity: 0.8; }
    }

    .auth-container {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 450px;
        padding: 0 1rem;
    }

    .auth-card {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 3rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(124, 58, 237, 0.2);
    }

    .auth-logo {
        text-align: center;
        margin-bottom: 2rem;
    }

    .auth-logo-icon {
        width: 60px;
        height: 60px;
        background: var(--primary-purple);
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .auth-logo-text {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-light);
    }

    .auth-title {
        text-align: center;
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-light);
        margin-bottom: 0.5rem;
    }

    .auth-subtitle {
        text-align: center;
        color: var(--text-muted);
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-light);
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 0.875rem 1rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(124, 58, 237, 0.3);
        border-radius: 10px;
        color: var(--text-light);
        font-size: 1rem;
        transition: all 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-purple);
        background: rgba(255, 255, 255, 0.08);
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
    }

    .form-control::placeholder {
        color: var(--text-muted);
    }

    .btn-primary {
        width: 100%;
        padding: 1rem;
        background: var(--primary-purple);
        border: none;
        border-radius: 10px;
        color: white;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background: var(--light-purple);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(124, 58, 237, 0.4);
    }

    .auth-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(124, 58, 237, 0.1);
    }

    .auth-footer a {
        color: var(--primary-purple);
        text-decoration: none;
        font-weight: 600;
    }

    .auth-footer a:hover {
        color: var(--light-purple);
    }

    .back-link {
        display: inline-block;
        margin-bottom: 2rem;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.875rem;
    }

    .back-link:hover {
        color: var(--text-light);
    }

    .help-block {
        color: #F87171;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .bonus-badge {
        background: rgba(16, 185, 129, 0.15);
        color: #34D399;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    /* ===== Force input text to be visible (fix Chrome autofill / text-fill issues) ===== */
    .form-control {
        color: var(--text-light) !important;
        caret-color: var(--text-light) !important;
        -webkit-text-fill-color: var(--text-light) !important; /* IMPORTANT */
        opacity: 1 !important;
    }

    /* Placeholder */
    .form-control::placeholder {
        color: var(--text-muted) !important;
        opacity: 1 !important;
    }

    /* Chrome autofill fix */
    input.form-control:-webkit-autofill,
    input.form-control:-webkit-autofill:hover,
    input.form-control:-webkit-autofill:focus,
    textarea.form-control:-webkit-autofill,
    select.form-control:-webkit-autofill {
        -webkit-text-fill-color: var(--text-light) !important;
        caret-color: var(--text-light) !important;
        transition: background-color 999999s ease-in-out 0s; /* remove yellow bg flash */
        box-shadow: 0 0 0px 1000px rgba(255, 255, 255, 0.05) inset !important; /* keep your dark bg */
        border: 1px solid rgba(124, 58, 237, 0.3) !important;
    }


</style>

<div class="auth-container">
    <a href="<?= Url::home() ?>" class="back-link">← Asosiy sahifaga qaytish</a>

    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">🎤</div>
            <div class="auth-logo-text">Ovoza</div>
        </div>

        <h1 class="auth-title">Hisob yaratish</h1>
        <p class="auth-subtitle">Bepul boshlang</p>

        <div class="bonus-badge">
            🎁 Ro'yxatdan o'tganingizda 10,000 UZS bonus!
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'signup-form',
            'options' => ['class' => ''],
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'form-label'],
                'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'help-block'],
            ],
        ]); ?>

        <?= $form->field($model, 'username')->textInput([
            'autofocus' => true,
            'placeholder' => 'Foydalanuvchi nomini tanlang',
        ])->label('Foydalanuvchi nomi') ?>

        <?= $form->field($model, 'email')->textInput([
            'placeholder' => 'email@example.com',
            'type' => 'email',
        ])->label('Email') ?>

        <?= $form->field($model, 'password')->passwordInput([
            'placeholder' => 'Kamida 6 ta belgi',
        ])->label('Parol') ?>

        <button type="submit" class="btn-primary">
            Ro'yxatdan o'tish
        </button>

        <?php ActiveForm::end(); ?>

        <div class="auth-footer">
            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">
                Allaqachon hisobingiz bormi?
            </p>
            <a href="<?= Url::to(['site/login']) ?>">
                Kirish →
            </a>
        </div>
    </div>
</div>