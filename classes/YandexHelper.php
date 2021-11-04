<?php

namespace Classes\Helpers;

use Exception;
use Monolog\Logger;
use Yandex\Market\Partner\Clients\OrderProcessingClient;
use Yandex\OAuth\Exception\AuthRequestException;
use Yandex\OAuth\OAuthClient;

class YandexHelper
{
    public $marketOauthClient;

    public  $logger;

    public function __construct(){
        $this->marketOauthClient = new OAuthClient(YANDEX_CLIENT_ID, YANDEX_CLIENT_SECRET);
        $this->logger = new Logger('name');
    }

    public function authorize() {
        if ($_REQUEST['state'] !== 'qwerty') {
            try {
                $response = $this->marketOauthClient->authRedirect(true, OAuthClient::CODE_AUTH_TYPE, 'qwerty');
                exit;
            } catch (Exception $e) {
                exit;
            }
        } else {
            try {
                $token = $this->marketOauthClient->requestAccessToken($_REQUEST['code']);
                return $token;
            } catch (AuthRequestException $ex) {
                throw new AuthRequestException($ex->getMessage());
            }
        }
    }

    public function getOrdersFromMarket() {
        $orderProcessingClient = new OrderProcessingClient(
            YANDEX_CLIENT_ID,
            $this->marketOauthClient->getAccessToken()
        );

        try {
            $orders = $orderProcessingClient->getOrders(YANDEX_CAMPAIN_ID)->getOrders();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }

        return $this->getOnlyNewOrders($orders);
    }

    public function getOnlyNewOrders($data): array
    {
        $result = array();

        foreach ($data as $order) {
            $creationTime = strtotime($order->getCreationDate());
            $currentTime = time();
            # TODO change sign to <=
            if ($currentTime - $creationTime >= FIVE_MINUTES) {
                $result[] = $order;
            }
        }

        return $result;
    }

    private function updateStocks($data) {

    }
}