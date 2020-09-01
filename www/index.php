<?php
/**
 * Single-page app for parse search pages
 * @autor Anatoliy Lazarev <software7528developer@yandex.ru>
 */

// composer
require 'vendor/autoload.php';

$route  = !empty($_GET['api']) ? 'api.php' : 'page.html';

include $route;
