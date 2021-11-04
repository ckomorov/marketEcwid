<?php

namespace Classes;

use GuzzleHttp\Client;
use Yandex\Market\Partner\Models\Buyer;
use Yandex\Market\Partner\Models\Item;
use Yandex\Market\Partner\Models\Order;
use Yandex\Market\Partner\Models\OrderInfo;

class EcwidHelper
{
    private $client;

    private $updateData = [];

    private $marketIds = [];

    public function __construct()
    {
        $this->client = new Client();
    }

    public function createOrders($data) {
        $orders = $this->prepareOrders($data);
//var_dump($orders, $data);die;
        $result = $this->pushOrders($orders);

        echo $result;
    }

    private function prepareOrders($data): array
    {
        $result = [];

        foreach ($data as $order) {
            $newOrder = [];
            $orderID = $order->getId();

            $this->marketIds[$orderID]['status'] = $order->getStatus();
            $this->marketIds[$orderID]['substatus'] = $order->getSubstatus();

            //(new OrderInfo())->getS
            $newOrder['externalOrderId'] = !empty($order->getId()) ? (string)$order->getId() : '';
            $newOrder['shippingPerson'] = $this->getPersonInfo($order);
            $newOrder['subtotal'] = $order->getItemsTotal();
            $newOrder['total'] = $order->getTotal();
            $newOrder['items'] = $this->getItemsInfo($order->getItems()->getAll());
            $newOrder['shippingOption'] = $this->getShippingOption($order);


            $result[] = $newOrder;
        }

        return $result;
    }

    private function pushOrders($orders) {
        if (empty($orders)) {
            return 'Empty orders';
        }

//        foreach ($orders as $order) {
//            try {
//                $response = $this->client->request(
//                    'POST',
//                    'https://app.ecwid.com/api/v3/' . ECWID_STORE_ID . '/orders?token=' . ECWID_PUBLIC_TOKEN,
//                    [
//                        'headers' => [
//                            'Accept' => 'application/json',
//                        ],
//                        'body' => json_encode($order)
//                    ]
//                );
//                echo $response->getStatusCode();
//            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
//                return $e->getMessage();
//            }


            $ids = array_keys($this->marketIds);
            $ecwidIDs = $this->getEcwidIdsByExternalIds($ids);
            $this->updateStatuses($ids);
        }
//    }

    private function updateStatuses($ids) {

    }

    private function getEcwidIdsByExternalIds($externalIDs) {
        $result = [];

        foreach ($externalIDs as $externalID) {
            try {
                $response = $this->client->request('GET', 'https://app.ecwid.com/api/v3/' . ECWID_STORE_ID . '/orders?token=' . ECWID_SECRET_TOKEN, [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);
                echo $response->getStatusCode();
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                echo $e->getMessage();
            }
        }
var_dump($response->getBody());die;
        return $result;
    }

    private function getPersonInfo($data): array
    {
        (new Buyer())->getId();

        $buyer = $data->getBuyer();
        $firstName = !empty($buyer->getFirstName()) ? $buyer->getFirstName() : '';
        $phone = !empty($buyer->getPhone()) ? $buyer->getPhone() : '';
        $lastName = !empty($buyer->getLastName()) ? $buyer->getLastName() : '';
        $middleName = !empty($buyer->getMiddleName()) ? ' ' . $buyer->getMiddleName() : '';

        $deliveryInfo = $data->getDelivery();
        $deliveryAddressInfo = $deliveryInfo->getAddress();
        $street = !empty($deliveryAddressInfo->getStreet()) ? $deliveryAddressInfo->getStreet() . ', ' : '';
        $house = !empty($deliveryAddressInfo->getHouse()) ? 'д. ' . $deliveryAddressInfo->getHouse() . ', ' : '';
        $apartment = !empty($deliveryAddressInfo->getApartment()) ? 'кв. ' . $deliveryAddressInfo->getApartment() . ', ' : '';
        $entrance = !empty($deliveryAddressInfo->getEntrance()) ? $deliveryAddressInfo->getEntrance() . ' подъезд, ' : '';
        $floor = !empty($deliveryAddressInfo->getFloor()) ? $deliveryAddressInfo->getFloor() . ' этаж' : '';

        $result['name'] = $firstName . ' ' . $lastName . $middleName;
        //$result['phone'] = $phone;
        $result['phone'] = '+79778540783';
        $result['country'] = $deliveryAddressInfo->getCountry();
        $result['city'] = $deliveryAddressInfo->getCity();
        $result['postalCode'] = $deliveryAddressInfo->getPostcode();
        $result['street'] = $street . $house . $apartment . $entrance . $floor;

        return $result;
    }

    private function getItemsInfo($data) : array {
        $result = [];

        foreach ($data as $item) {
            $newItem = [];

            $newItem['sku'] = $item->getOfferId();
            $newItem['name'] = $item->getOfferName();
            $newItem['price'] = $item->getPrice();
            $newItem['quantity'] = $item->getCount();

            $result[] = $newItem;
        }

        return $result;
    }

    private function getShippingOption($data) : array {
        $result = [];

        $result['shippingRate'] = !empty($data->getDelivery()->getPrice()) ? $data->getDelivery()->getPrice() : null;
        $result['fulfillmentType'] = 'DELIVERY';
        $result['shippingMethodName'] = 'Доставка курьером';

        return $result;
    }

    public function getStocksFromEcwid() {
        $currentStocks = $this->getStocks();
        $preparedStocks = $this->prepareStocks($currentStocks);
        return $preparedStocks;
    }

    private function getStocks() {
        $offset = 0;
        $result = [];

        do {
            try {
                $response = $this->client->request(
                    'GET',
                    'https://app.ecwid.com/api/v3/' . ECWID_STORE_ID . '/products?token=' . ECWID_SECRET_TOKEN . '&offset=' . $offset,
                    ['headers' => ['Accept' => 'application/json',]]
                );
                $items = (json_decode($response->getBody()))->items;
                foreach ($items as $item) {
                    $result[] = $item;
                }
                $offset = $offset + 100;
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                echo ($e->getMessage());
                exit;
            }
        } while(!empty($items));

        return $result;
    }

    private function prepareStocks($data) {
        $result = [];

        foreach ($data as $item) {
            $result[$item->id] = [
                'sku' => $item->sku,
                'quantity' => $item->quantity
            ];
        }

        return $result;
    }

}