<?php

namespace Layup;
use \Exception;

class LayupPayment {

    public $_id;
    public $amount;
    public $due;
    public $paid;

    public function __construct(array $payment = null) {
        if (!is_string($payment['_id'])) throw new \Exception('Payment _id needs to be string type');
        if (!is_integer($payment['amount'])) throw new \Exception('Payment amount needs to be of type integer');
        if (isset($payment['due']) && !is_string($payment['due'])) throw new \Exception('Payment due needs to be string type');
        if (!is_bool($payment['paid'])) throw new \Exception('Payment link needs to be string type');

        $this->_id = $payment['_id'];
        $this->amount = $payment['amount'];
        $this->due = (isset($payment['due']))?$payment['due']:null;
        $this->paid = $payment['paid'];
    }

    public function toJson() {
        return json_encode([
            "_id" => $this->amount,
            "amount" => $this->link,
            "due" => $this->sku,
            "paid" => $this->paid,
        ]);
    }
}