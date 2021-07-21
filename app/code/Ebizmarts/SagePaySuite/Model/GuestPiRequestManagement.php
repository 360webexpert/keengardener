<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite;

class GuestPiRequestManagement extends PiRequestManagement
{

    /**
     * @param mixed $cartId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuoteById($cartId)
    {
        $quoteIdMask = $this->getQuoteIdMaskFactory()->create()->load($cartId, 'masked_id');

        return $this->getQuoteRepository()->get($quoteIdMask->getQuoteId());
    }
}
