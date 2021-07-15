<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Controller\Adminhtml\ProductsPage;

use Magento\Backend\App\Action;

/**
 * Class MassDelete
 * @package Mageplaza\LayeredNavigationUltimate\Controller\Adminhtml\ProductsPage
 */
class MassDelete extends Action
{
    /**
     * @return void
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('page_id');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select page(s).'));
        } else {
            $numOfSuccess = 0;
            foreach ($ids as $id) {
                try {
                    $page = $this->_objectManager->create('Mageplaza\LayeredNavigationUltimate\Model\ProductsPage')->load($id);
                    $page->delete();
                    $numOfSuccess++;
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Cannot delete page with ID %1', $id));
                }
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $numOfSuccess));
        }

        $this->_redirect('*/*/');
    }
}
