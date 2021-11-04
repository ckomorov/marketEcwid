<?php

use Classes\EcwidHelper;
use Classes\Helpers\YandexHelper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require __DIR__ . '/vendor/autoload.php';
require 'config/config.inc';

// Init logger
$logger = new Logger('loggerName');
$logger->pushHandler(new StreamHandler('logs/' . date('Y-m-d') . '.log', Logger::INFO));

// Authorization in Market
try {
    $yandexHelper = new YandexHelper();
    $yandexHelper->authorize();
} catch (\Yandex\OAuth\Exception\AuthRequestException $e) {
    $logger->warning($e->getMessage());
    exit;
}
//$json = '{"warehouseId": 146173, "skus": [ "P3001", "P3008", "P3004", "P3005" ] }';
//$ecwidHelper = new EcwidHelper();
//$stocksFromEcwid = $ecwidHelper->getStocksFromEcwid();
//$logger->warning(json_encode($stocksFromEcwid));
//var_dump($stocksFromEcwid);
//die;


if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'create') {
    // Request to Market for orders
    $ordersFromMarket = $yandexHelper->getOrdersFromMarket();
var_dump($ordersFromMarket);die;
    // Request to Ecwid
    $ecwidHelper->createOrders($ordersFromMarket);
} else {
    $ecwidHelper->updateStocks();
}











