<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Walmart\Listing\Product\Action\Type\Relist;

/**
 * Class \Ess\M2ePro\Model\Walmart\Listing\Product\Action\Type\Relist\Request
 */
class Request extends \Ess\M2ePro\Model\Walmart\Listing\Product\Action\Type\Request
{
    //########################################

    protected function beforeBuildDataEvent()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        unset($additionalData['synch_template_list_rules_note']);

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $this->getListingProduct()->save();

        parent::beforeBuildDataEvent();
    }

    //########################################

    protected function getActionData()
    {
        $data = array_merge(
            [
                'sku'  => $this->getWalmartListingProduct()->getSku(),
                'wpid' => $this->getWalmartListingProduct()->getWpid(),
            ],
            $this->getQtyData(),
            $this->getLagTimeData(),
            $this->getPriceData(),
            $this->getPromotionsData()
        );

        return $data;
    }

    //########################################
}
