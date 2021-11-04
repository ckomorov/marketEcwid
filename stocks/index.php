<?php

use Classes\EcwidHelper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

error_reporting(1);
require '../vendor/autoload.php';
require '../config/config.inc';

$logger = new Logger('loggerName');
$logger->pushHandler(new StreamHandler('../logs/' . date('Y-m-d') . '.log', Logger::INFO));

$logger->info(json_encode($_REQUEST));


$response = new stdClass();
$data = new stdClass();
$data->warehouseId = 146173;
$data->sku = 1;
$data->items = [];
$response->skus = [
    $data
];

var_dump($response);

$json = '{"warehouseId": 146173, "skus": ["P3001", "P3008", "P3004", "P3005"] }';
$ecwidHelper = new EcwidHelper();
//$stocksFromEcwid = $ecwidHelper->getStocksFromEcwid();

var_dump(json_decode($json));
$js = '{"skus":[{"sku": "123", "warehouseId": "123","items":[{"type": "FIT","count": 10,"updatedAt": "today"}]}]}';

var_dump(json_decode($js));
die;

