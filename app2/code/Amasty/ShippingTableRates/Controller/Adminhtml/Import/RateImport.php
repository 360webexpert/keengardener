<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Controller\Adminhtml\Import;

use Amasty\ShippingTableRates\Controller\Adminhtml\AbstractImport;

class RateImport extends AbstractImport
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_ShippingTableRates::rates_import');
        $resultPage->getConfig()->getTitle()->prepend(__('Import Shipping Table Rates'));
        $resultPage->addBreadcrumb(__('Import Shipping Table Rates'), __('Import Shipping Table Rates'));

        return $resultPage;
    }
}
