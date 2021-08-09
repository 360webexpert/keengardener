<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing;

/**
 * Class \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\RunRelistProducts
 */
class RunRelistProducts extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\ActionAbstract
{
    public function execute()
    {
        if ($this->getHelper('Data')->jsonDecode($this->getRequest()->getParam('is_realtime'))) {
            return $this->processConnector(
                \Ess\M2ePro\Model\Listing\Product::ACTION_RELIST
            );
        }

        return $this->scheduleAction(
            \Ess\M2ePro\Model\Listing\Product::ACTION_RELIST
        );
    }
}
