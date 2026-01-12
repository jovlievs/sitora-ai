<?php
namespace console\controllers;

use yii\console\Controller;
use common\models\User;
use common\models\SystemSetting;

class TestController extends Controller
{
    public function actionIndex()
    {
        echo "Testing models...\n\n";

        // Test SystemSetting
        $price = SystemSetting::getDefaultPricePerMinute();
        echo "Default price: {$price} UZS\n";

        echo "\n✅ Models loaded successfully!\n";
    }
}