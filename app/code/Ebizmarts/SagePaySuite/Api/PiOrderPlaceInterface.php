<?php

namespace Ebizmarts\SagePaySuite\Api;

interface PiOrderPlaceInterface
{
    public function pay();

    public function processPayment();

    public function getRequest();

    /**
     * @return \Ebizmarts\SagePaySuite\Api\Data\PiResultInterface
     */
    public function placeOrder();
}
