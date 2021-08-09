<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Settings\Motors;

/**
 * Class \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Settings\Motors\CloseInstruction
 */
class CloseInstruction extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing
{
    //########################################

    public function execute()
    {
        $this->getHelper('Module')->getRegistry()->setValue('/ebay/motors/instruction/is_shown/', 1);
    }

    //########################################
}
