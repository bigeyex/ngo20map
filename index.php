<?php
define('THINK_PATH', './ThinkPHP/');
define('APP_NAME', 'edu20');
define('APP_PATH', '.');
define('NO_CACHE_RUNTIME',True); //for debug use only
define('SALT_KEY', '&73uFb('); //for encrypt use
require(THINK_PATH . "ThinkPHP.php");
require('./Lib/Util/function.php');
require_once './Lib/Util/EduRelationModel.class.php';
App::run();
?>
