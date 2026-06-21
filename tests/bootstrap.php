<?php

/**
 * PHPUnit bootstrap: defines the Yii environment constants and loads the Yii
 * framework so the test suite can boot a mock application.
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
