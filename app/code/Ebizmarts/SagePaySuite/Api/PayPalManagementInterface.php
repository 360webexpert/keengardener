<?php

namespace Ebizmarts\SagePaySuite\Api;

/**
 * @api
 */
interface PayPalManagementInterface
{

    /**
     * @param string $cartId
     * @return \Ebizmarts\SagePaySuite\Api\Data\ResultInterface
     */
    public function savePaymentInformationAndPlaceOrder($cartId);

    /**
     * @param string $cartId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuoteById($cartId);
}
