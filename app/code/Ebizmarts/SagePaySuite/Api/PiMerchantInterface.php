<?php

namespace Ebizmarts\SagePaySuite\Api;

/**
 * @api
 */
interface PiMerchantInterface
{

    /**
     * Creates a merchant session key (MSK).
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Ebizmarts\SagePaySuite\Api\Data\ResultInterface
     */
    public function getSessionKey(\Magento\Quote\Api\Data\CartInterface $quote = null);
}
