<?php

namespace Ebizmarts\SagePaySuite\Model;

use \Ebizmarts\SagePaySuite\Api\GuestPayPalManagementInterface;

class GuestPayPalRequestManagement extends PayPalRequestManagement implements GuestPayPalManagementInterface
{
    /**
     * {@inheritDoc}
     */
    public function getQuoteById($cartId)
    {
        $quoteIdMask = $this->getQuoteIdMaskFactory()->create()->load($cartId, 'masked_id');

        return $this->getQuoteRepository()->get($quoteIdMask->getQuoteId());
    }
}
