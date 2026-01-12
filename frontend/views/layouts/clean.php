<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;

?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>

        <!-- jQuery (needed for AJAX) -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                background: #0F0F1E;
                color: #E2E8F0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                min-height: 100vh;
            }

            /* Remove Bootstrap's default styles that conflict */
            .container, .container-fluid {
                padding-left: 15px;
                padding-right: 15px;
            }
        </style>

        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <?= $content ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>