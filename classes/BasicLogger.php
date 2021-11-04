<?php

namespace Classes\Logger;

use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;

class BasicLogger
{
    public $log;

    public function __construct()
    {
        $this->log = new Monolog('log');
        $this->log->pushHandler(new StreamHandler(__DIR__ . '/logs/123.log', Monolog::INFO));
    }


}