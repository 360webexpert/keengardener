<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Listing\Product\Action\Type\Stop;

/**
 * Class \Ess\M2ePro\Model\Ebay\Listing\Product\Action\Type\Stop\Request
 */
class Request extends \Ess\M2ePro\Model\Ebay\Listing\Product\Action\Type\Request
{
    //########################################

    /**
     * @return array
     */
    public function getActionData()
    {
        return [
            'item_id' => $this->getEbayListingProduct()->getEbayItemIdReal()
        ];
    }

    //########################################

    protected function initializeVariations()
    {
        return null;
    }

    // ---------------------------------------

    protected function prepareFinalData(array $data)
    {
        return $data;
    }

    //########################################
}
