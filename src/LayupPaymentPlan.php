<?php
namespace Layup;

use Layup\LayupProduct;
use Layup\LayupIntegrator;
use Layup\LayupPaymentPlan;
use Layup\LayupPayment;
use Layup\LayupORM;
use Carbon\Carbon;
use \Exception;

class LayupPaymentPlans {

    public function __construct() {}

    public static function get() {
        $layupApi = new LayupIntegrator();
        $paymentPlans = $layupApi->getPaymentPlans();
        return array_map(function ($paymentPlan) {
            $paymentPlanObj = new LayupPaymentPlan();
            $paymentPlanObj->__toObject($paymentPlan);
            return $paymentPlanObj;
        }, $paymentplans);
    }
}

class LayupPaymentPlan implements LayupORM {

    private $layupApi;

    public $automaticBilling;
    public $_id;
    public $order;
    public $orderId;
    public $benefactorId;
    public $benefactor;
    public $completed;
    public $months;
    public $amountDue;
    public $depositDue;
    public $depositPaid;
    public $depositPercentage;
    public $plan;
    public $payments;
    public $active;
    public $timestamp;

    public function __construct(array $paymentPlan = null) {
        $this->layupApi = new LayupIntegrator();
        $this->__init();
        if($paymentPlan) {
            $this->__toObject($paymentPlan);
        }
    }

    public function __init() {
        $this->automaticBilling = null;
        $this->_id = null;
        $this->order = null;
        $this->orderId = null;
        $this->benefactorId = null;
        $this->benefactor = null;
        $this->completed = null;
        $this->months = null;
        $this->amountDue = null;
        $this->depositDue = null;
        $this->depositPaid = null;
        $this->depositPercentage = null;
        $this->plan = null;
        $this->payments = null;
        $this->active = null;
        $this->timestamp = null;
    }

    public function toJson() {
        return json_encode([
            "automaticBilling" => $this->automaticBilling,
            "_id" => $this->_id,
            "order" => $this->order,
            "orderId" => $this->orderId,
            "benefactorId" => $this->benefactorId,
            "benefactor" => $this->benefactor,
            "completed" => $this->completed,
            "months" => $this->months,
            "amountDue" => $this->amountDue,
            "depositDue" => $this->depositDue,
            "depositPaid" => $this->depositPaid,
            "depositPercentage" => $this->depositPercentage,
            "plan" => $this->plan,
            "payments" => $this->payments,
            "active" => $this->active,
            "timestamp" => $this->timestamp,
        ]);
    }

    public static function find($_id) {
        $layupApi = new LayupIntegrator();
        $paymentPlan = $layupApi->getPaymentPlan($_id);
        if (!is_array($paymentPlan) || !$paymentPlan || $paymentPlan === NULL || !(count($paymentPlan) > 0) ) {
            return new LayupPaymentPlan();
        }
        return new LayupPaymentPlan($paymentPlan);
    }

    public function populate() {
        $layupApi = new LayupIntegrator();
        $paymentPlan = $layupApi->getPaymentPlan($this->_id);
        $this->__toObject($paymentPlan);
        return $this;
    }

    public function __toObject(array $paymentPlan) {
        if (isset($paymentPlan['automaticBilling'])) $this->automaticBilling = $paymentPlan['automaticBilling'];
        if (isset($paymentPlan['_id'])) $this->_id = $paymentPlan['_id'];
        if (isset($paymentPlan['order'])) $this->order = $paymentPlan['order'];
        if (isset($paymentPlan['orderId'])) $this->orderId = $paymentPlan['orderId'];
        if (isset($paymentPlan['benefactorId'])) $this->benefactorId = $paymentPlan['benefactorId'];
        if (isset($paymentPlan['benefactor'])) $this->benefactor = $paymentPlan['benefactor'];
        if (isset($paymentPlan['completed'])) $this->completed = $paymentPlan['completed'];
        if (isset($paymentPlan['months'])) $this->months = $paymentPlan['months'];
        if (isset($paymentPlan['amountDue'])) $this->amountDue = $paymentPlan['amountDue'];
        if (isset($paymentPlan['depositDue'])) $this->depositDue = $paymentPlan['depositDue'];
        if (isset($paymentPlan['depositPaid'])) $this->depositPaid = $paymentPlan['depositPaid'];
        if (isset($paymentPlan['depositPercentage'])) $this->depositPercentage = $paymentPlan['depositPercentage'];
        if (isset($paymentPlan['plan'])) $this->plan = $paymentPlan['plan'];
        if (isset($paymentPlan['payments'])){

            $this->payments = array_map(function ($payment) {
                return new LayupPayment($payment);
            }, $paymentPlan['payments']);
        }
        if (isset($paymentPlan['active'])) $this->active = $paymentPlan['active'];
        if (isset($paymentPlan['timestamp'])) $this->timestamp = $paymentPlan['timestamp'];
        return $this;
    }


    private function validateAutomaticBilling () {
        if (!is_bool($this->automaticBilling)) throw new \Exception('PaymentPlan automaticBilling must be of type array.');
    }

    private function validateMonths () {
        if (!is_integer($this->months)) throw new \Exception('PaymentPlan months must be of type integer');
    }


    private function __preSave() {
        $this->validateAutomaticBilling();
        $this->validateMonths();
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

    public function __save() {
        try {
            $order = $this->layupApi->createOrder($this->toJson());
            $this->__orderToObject($order);
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
            $this->__orderToObject($order);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return $this;
    }
}
