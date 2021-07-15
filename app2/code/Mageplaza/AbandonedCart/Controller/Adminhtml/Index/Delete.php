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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Controller\Adminhtml\Index;

use Exception;
use Mageplaza\AbandonedCart\Controller\Adminhtml\AbandonedCart;

/**
 * Class Delete
 * @package Mageplaza\AbandonedCart\Controller\Adminhtml\Index
 */
class Delete extends AbandonedCart
{
    /**
     * Delete abandoned cart email log
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $log = $this->logsFactory->create()->load($id);
            if ($log->getId()) {
                $log->setDisplay(false)->save();
                $this->messageManager->addSuccessMessage(__('Delete success.'));
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('There is an error occurs. Please try again later.'));
        }

        $this->_redirect('abandonedcart/*/report');
    }
}
