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
 * Class Delete
 * @package Mageplaza\LayeredNavigationUltimate\Controller\Adminhtml\ProductsPage
 */
class Delete extends Action
{
    /**
     * Delete page item
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('page_id');
        try {
            $page = $this->_objectManager->create('Mageplaza\LayeredNavigationUltimate\Model\ProductsPage')->load($id);
            if ($page && $page->getId()) {
                $page->delete();
                $this->messageManager->addSuccessMessage(__('Delete successfully !'));
            } else {
                $this->messageManager->addErrorMessage(__('Cannot find page to delete.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->_redirect('*/*/');
    }
}
