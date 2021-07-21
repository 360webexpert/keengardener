<?php

namespace Ebizmarts\SagePaySuite\Block\Form;

class Pi extends \Magento\Payment\Block\Form\Cc
{

    /**
     * @return \Magento\Backend\Model\Session\Quote
     */
    public function getBackendSession()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get("Magento\Backend\Model\Session\Quote");
    }
}
