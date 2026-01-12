<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: PHP is working<br>";

echo "Step 2: Checking paths...<br>";
echo "Current dir: " . __DIR__ . "<br>";
echo "Parent dir: " . dirname(__DIR__) . "<br>";

$bootstrapPath = __DIR__ . '/../config/bootstrap.php';
echo "Bootstrap path: {$bootstrapPath}<br>";
echo "Bootstrap exists: " . (file_exists($bootstrapPath) ? 'YES' : 'NO') . "<br>";

if (file_exists($bootstrapPath)) {
    echo "Step 3: Loading bootstrap...<br>";
    require($bootstrapPath);
    echo "Bootstrap loaded!<br>";
}

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
echo "Autoload path: {$autoloadPath}<br>";
echo "Autoload exists: " . (file_exists($autoloadPath) ? 'YES' : 'NO') . "<br>";

if (file_exists($autoloadPath)) {
    echo "Step 4: Loading autoload...<br>";
    require($autoloadPath);
    echo "Autoload loaded!<br>";
}

echo "<br>If you see this, PHP is working but something else is wrong!<br>";
echo "Check the paths above and tell me which file is missing.";
?>