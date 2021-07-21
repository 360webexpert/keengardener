<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite;
use \Ebizmarts\SagePaySuite\Api\GuestFormManagementInterface;

class GuestFormRequestManagement extends FormRequestManagement implements GuestFormManagementInterface
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
