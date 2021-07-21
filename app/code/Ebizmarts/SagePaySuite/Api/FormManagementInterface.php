<?php

namespace Ebizmarts\SagePaySuite\Api;

/**
 * @api
 */
interface FormManagementInterface
{

    /**
     * @param string $cartId
     * @return \Ebizmarts\SagePaySuite\Api\Data\FormResultInterface
     */
    public function getEncryptedRequest($cartId);

    /**
     * @param mixed $cartId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuoteById($cartId);
}
