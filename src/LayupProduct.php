<?php

namespace Layup;
use \Exception;

class LayupProduct {

    public $amount;
    public $link;
    public $sku;

    public function __construct($amount, $link, $sku) {
        if (!is_integer($amount)) throw new \Exception('Product amount needs to be of type integer');
        if (!is_string($link)) throw new \Exception('Porudct link needs to be string type');
        if (!is_string($sku)) throw new \Exception('Product sku needs to be string type');

        $this->amount = $amount;
        $this->link = $link;
        $this->sku = $sku;
    }

    public function toJson() {
        return json_encode([
            "amount" => $this->amount,
            "link" => $this->link,
            "sku" => $this->sku
        ]);
    }
}