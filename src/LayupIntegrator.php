<?php
namespace Layup;

class LayupIntegrator {

    private $apiToken;
    private $apiUrl;
    private $reqClient;

    public function __construct() {
        $this->apiToken = config('app.layup_token');
        $this->apiUrl = config('app.layup_url');
        $this->reqClient = new \GuzzleHttp\Client();
    }

    public static function getApiUrl() {
        return config('app.layup_token');
    }

    public static function getApiKey() {
        return config('app.layup_url');
    }

    public function createUrl(string $resource) {
        return "$this->apiUrl/v1/${resource}";
    }

    public function post(string $resource, string $data) {
        $url = $this->createUrl($resource);
        $headers = [
            'Content-Type' => 'application/json',
            'apikey' => $this->apiToken,
        ];
        $req = $this->reqClient->request('POST', $url, ['headers' => $headers, 'body' => $data]);
        return $req->getBody()->getContents();
    }

    public function put(string $resource, string $data) {
        $url = $this->createUrl($resource);
        $headers = [
            'Content-Type' => 'application/json',
            'apikey' => $this->apiToken,
        ];
        $req = $this->reqClient->request('PUT', $url, ['headers' => $headers, 'body' => $data]);
        return $req->getBody()->getContents();
    }

    public function get($resource) {
        $url = $this->createUrl($resource);
        $headers = [
            'accept' => 'application/json',
            'Content-Type' => 'application/json',
            'apikey' => $this->apiToken,
        ];
        
        $req = $this->reqClient->request('GET', $url, ['headers' => $headers]);
        return $req->getBody()->getContents();
    }

    public function getPaymentPlans() {
        $paymentPlans = $this->get("payment-plan");
        return json_decode($paymentPlan, true);
    }

    public function getPaymentPlan($planId) {
        $paymentPlans = $this->get("payment-plan/${planId}");
        return json_decode($paymentPlans, true);
    }

    public function createPaymentPlan($paymentPlan) {
        $paymentPlan = $this->post("payment-plan", $paymentPlan);
        return json_decode($paymentPlan, true);
    }

    public function updatePaymentPlan($_id, $paymentPlan) {
        $paymentPlan = $this->put("payment-plan/${_id}", $paymentPlan);
        return json_decode($paymentPlan, true);
    }

    public function getOrder($orderId) {
        $order = $this->get("orders/${orderId}?populate=plans");
        return json_decode($order, true);
    }

    public function getOrders() {
        $orders = $this->get('orders?populate=plans');
        return json_decode($orders, true)["orders"];
    }

    public function createOrder($order) {
        $order = $this->post('orders', $order);
        return json_decode($order, true);
    }

    public function updateOrder($_id, $order) {
        $order = $this->post('orders/' . $_id, $order);
        return json_decode($order, true);
    }

    public function verifyPayment($paymentId) {
        $payment = $this->get("payments/${paymentId}?verify=true");
        return json_decode($payment, true);
    }
}