<?php

declare(strict_types=1);
session_start();
define('ROOT', dirname(__FILE__, 3));
define('HOST', $_SERVER['HTTP_HOST']);
#DIRETÓRIO DAS VIEWS
define('DIR_VIEWS', ROOT . '/App/View');
#EXTENSÃO PADRÃO DAS VIEWS
define('EXT_VIEWS', '.html');
define('SECRET_KEY', '35ddde83-e1c9-475b-8a9c-2ab189232785');
