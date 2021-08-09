<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Log\Listing\Product;

use Ess\M2ePro\Block\Adminhtml\Log\Listing\Product\AbstractView;

/**
 * Class \Ess\M2ePro\Block\Adminhtml\Ebay\Log\Listing\Product\View
 */
class View extends AbstractView
{
    //########################################

    protected function getComponentMode()
    {
        return \Ess\M2ePro\Helper\View\Ebay::NICK;
    }

    //########################################

    protected function _toHtml()
    {
        $supportHelper = $this->helperFactory->getObject('Module_Support');
        $message = <<<TEXT
This Log contains information about the actions applied to M2E Pro Listings and related Items.<br/><br/>
Find detailed info in <a href="%url%" target="_blank">the article</a>.
TEXT;
        $helpBlock = $this->createBlock('HelpBlock')->setData([
            'content' => $this->__(
                $message,
                $supportHelper->getDocumentationArticleUrl('x/y5NaAQ#Logs&Events-M2EProListinglogs')
            )
        ]);

        return $helpBlock->toHtml() . parent::_toHtml();
    }

    //########################################
}
