<?php

/**
 * фаил настроек
 */

//> константы для обращения к контроллерам
define('PathPrefix', '../controllers/');
define('PathPostfix', 'Controller.php');
//<

//> используемый шаблон
//$template = 'default';
$template = 'texturia';
$templateAdmin = 'admin';


// константы, пути к файлам шаблонов(*.tpl)
define('TemplatePrefix', "../views/{$template}/");
define('TemplateAdminPrefix', "../views/{$templateAdmin}/");
define('TemplatePostfix', '.tpl');

// пути к файлам шаблонов в вебпространстве
define('TemplateWebPath', "/www/templates/{$template}/");
define('TemplateAdminWebPath', "/www/templates/{$templateAdmin}/");

// путь к картинкам и файлам папки: js
define('ImageWebPath', "/www");
//<

//> инициализация шаблонизатора Smarty
// putt full path to Smarty.class.php
require('../library/Smarty/libs/Smarty.class.php');

// создаём object $smarty (объект шаблонизатора)
$smarty = new Smarty();

// инициализируем значения объекта: $smarty
$smarty->setTemplateDir(TemplatePrefix);
$smarty->setCompileDir('../tmp/smarty/templates_c');
$smarty->setCacheDir('../tmp/smarty/cache');
$smarty->setConfigDir('../library/Smarty/configs');

// инициализируем объявленные ранее переменные
$smarty->assign('TemplateWebPath', TemplateWebPath);
$smarty->assign('ImageWebPath', ImageWebPath);
//<
