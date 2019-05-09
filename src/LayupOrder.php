<?php
namespace Layup;

use Layup\LayupORM;
use Layup\LayupProduct;
use Layup\LayupIntegrator;
use Layup\LayupPaymentPlan;
use Carbon\Carbon;
use \Exception;

class LayupOrders {

    public function __construct() {}

    public static function get() {
        $layupApi = new LayupIntegrator();
        $orders = $layupApi->getOrders();
        return array_map(function ($order) {
            $ordersObj = new LayupOrder();
            $ordersObj->__toObject($order);
            return $ordersObj;
        }, $orders);
    }
}

class LayupOrder implements LayupORM {

    private $layupApi;

    public $_id;
    public $endDateMax;
    public $endDateMin;
    public $products = [];
    public $reference;
    public $name;
    public $imageUrl;
    public $depositPerc;
    public $timestamp;
    public $plans = [];
    public $absorbsFee = true;
    public $initiatorId;
    public $state;

    public $confirmedPayments = [];
    public $unconfirmedPayments = [];
    public $upcomingPayments = [];

    public function __construct(array $order = null) {
        $this->layupApi = new LayupIntegrator();
        $this->__init();
        if($order)  {
            $this->__toObject($order);
        }
    }

    public function __init() {
        $this->_id = null;
        $this->endDateMax = null;
        $this->endDateMin = null;
        $this->products = null;
        $this->reference = null;
        $this->name = null;
        $this->imageUrl = null;
        $this->depositPerc = null;
        $this->plans = [];
        $this->initiatorId = null;
    }

    public function toJson() {
        return json_encode([
            "name" => $this->name,
            "reference" => $this->reference,
            "imageUrl" => $this->imageUrl,
            "endDateMax" => $this->endDateMax,
            "endDateMin" => $this->endDateMin,
            "products" => $this->products,
            "depositPerc" => $this->depositPerc,
            "absorbsFee" => $this->absorbsFee,
            "initiatorId" => $this->initiatorId
        ]);
    }

    public static function find($_id) {
        $layupApi = new LayupIntegrator();
        $order = $layupApi->getOrder($_id);
        if (!is_array($order) || !$order || $order === NULL || !(count($order) > 0) ) {
            return new LayupOrder();
        }
        return new LayupOrder($order);
    }

    public function save() {
        $this->__preSave();
        if ($this->_id) {
            $this->__update();
        } else {
            $this->__save();
        }
        return $this;
    }

    public function getPayments() {
        if(!$this->plans || !count($this->plans) > 0) return [];
        foreach($this->plans AS $paymentPlan) {
            $paymentPlan->populate();
            foreach($paymentPlan->payments AS $payment) {
                if($payment->paid) {
                    array_push($this->confirmedPayments, $payment);
                } else {
                    array_push($this->unconfirmedPayments, $payment);
                }
                array_push($this->upcomingPayments, $payment);
            }
        }
        return $this;
    }

    public function __toObject(array $order) {
        if(isset($order['_id'])) $this->_id = $order['_id'];
        if(isset($order['endDateMax'])) $this->endDateMax = $order['endDateMax'];
        if(isset($order['endDateMin'])) $this->endDateMin = $order['endDateMin'];
        if(isset($order['products'])) $this->products = $order['products'];
        if(isset($order['reference'])) $this->reference = $order['reference'];
        if(isset($order['name'])) $this->name = $order['name'];
        if(isset($order['imageUrl'])) $this->imageUrl = $order['imageUrl'];
        if(isset($order['plans'])) $this->plans = array_map(function ($plan) {return new LayupPaymentPlan($plan);}, $order["plans"]);
        if(isset($order['initiatorId'])) $this->initiatorId = $order['initiatorId'];
        if(isset($order['state'])) $this->state = $order['state'];
        return $this;
    }

    public function __save() {
        try {
            $order = $this->layupApi->createOrder($this->toJson());
            $this->__toObject($order);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return $this;
    }

    public function __update() {
        try {
            if(!$this->_id) throw new \Exception('Order _id is not set');
            if(!$this->_id) throw new \Exception('Order _id must be of type string');
            $order = $this->layupApi->updateOrder($this->_id, $this->toJson());
            $this->__toObject($order);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return $this;
    }

    private function __preSave() {
        $this->validateName();
        $this->validateReference();
        $this->validateImageUrl();
        $this->validateDates();
        $this->validateDepositPerc();
        $this->validateProducts();
    }

    private function checkDates(string $date) {
        return (bool) strtotime($date);
    }

    private function validateDates() {
        if (!is_array($this->products)) throw new \Exception('Order products must be of type array.');
        if (!is_string($this->endDateMax)) throw new \Exception('Order endDateMax must be of type string.');
        if (!is_string($this->endDateMin)) throw new \Exception('Order endDateMin must be of type string.');
        if(!$this->checkDates($this->endDateMax)) throw new \Exception('Order endDateMax must be a valid date format');
        if(!$this->checkDates($this->endDateMin)) throw new \Exception('Order endDateMin must be a valid date format');
    }

    private function validateProducts() {
        foreach($this->products as $i => $product) {
            if (!is_object($product)) throw new \Exception('Order products['.$i.'] must be of type Object.');
            if (!$product instanceof LayupProduct) throw new \Exception('Order products['.$i.'] Object must be instance of LayupProduct.');
        }
    }

    private function validateName() {
        if (!is_string($this->name)) throw new \Exception('Order name must be of type string.');
    }

    private function validateDepositPerc() {
        if (!is_numeric($this->depositPerc)) throw new \Exception('Order depositPerc must be of valid number type.');
    }

    private function validateReference() {
        if (!is_string($this->reference)) throw new \Exception('Order reference must be of type string.');
    }

    private function validateImageUrl() {
        if (!is_string($this->imageUrl)) throw new \Exception('Order imageUrl must be of type string.');
    }

}
