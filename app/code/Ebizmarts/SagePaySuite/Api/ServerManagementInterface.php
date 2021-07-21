<?php

namespace Ebizmarts\SagePaySuite\Api;

/**
 *
 * @api
 */
interface ServerManagementInterface
{

    /**
     * Set payment information and place order for a specified cart.
     *
     * @param mixed $cartId
     * @param bool $save_token
     * @param string $token
     * @return \Ebizmarts\SagePaySuite\Api\Data\ResultInterface
     */
    public function savePaymentInformationAndPlaceOrder($cartId, $save_token, $token);

    /**
     * @param mixed $cartId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuoteById($cartId);
}
