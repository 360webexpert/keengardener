<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Settings\Motors;

/**
 * Class \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Settings\Motors\AddView
 */
class AddView extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing
{
    //########################################

    public function execute()
    {
        $motorsType = $this->getRequest()->getParam('motors_type');

        if (!$this->wasInstructionShown()) {
            /** @var \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\View\Settings\Motors\Instruction $block */
            $block = $this->createBlock('Ebay_Listing_View_Settings_Motors_Instruction');

            $this->setAjaxContent($block);

            return $this->getResult();
        }

        /** @var \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\View\Settings\Motors\Add $block */
        $block = $this->createBlock('Ebay_Listing_View_Settings_Motors_Add');
        $block->setMotorsType($motorsType);

        $this->setAjaxContent($block);

        return $this->getResult();
    }

    //########################################

    public function wasInstructionShown()
    {
        return $this->getHelper('Module')->getRegistry()->getValue('/ebay/motors/instruction/is_shown/');
    }

    //########################################
}
