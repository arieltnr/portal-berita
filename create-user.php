<?php
// create-user.php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/common/config/bootstrap.php';

use yii\base\InvalidConfigException;
use yii\console\Application;
use common\models\User;

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/console/config/main.php',
    require __DIR__ . '/console/config/main-local.php'
);

$application = new Application($config);

// Buat user
$user = new User();
$user->username = 'admin';
$user->email = 'admin@example.com';
$user->setPassword('admin'); // plain password: 'admin'
$user->generateAuthKey();
$user->status = User::STATUS_ACTIVE;

if ($user->save()) {
    echo "✅ User 'admin' berhasil dibuat!\n";
} else {
    echo "❌ Gagal: " . print_r($user->errors, true);
}