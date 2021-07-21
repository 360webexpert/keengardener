<?php

namespace Ebizmarts\SagePaySuite\Api;

use Ebizmarts\SagePaySuite\Api\Data\PiRequest;

/**
 *
 * @api
 */
interface PiManagementInterface
{
    /**
     * Set payment information and place order for a specified cart.
     *
     * @param mixed $cartId
     * @param PiRequest $requestData
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return \Ebizmarts\SagePaySuite\Api\Data\PiResultInterface
     */
    public function savePaymentInformationAndPlaceOrder($cartId, PiRequest $requestData);

    /**
     * @param mixed $cartId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuoteById($cartId);
}
