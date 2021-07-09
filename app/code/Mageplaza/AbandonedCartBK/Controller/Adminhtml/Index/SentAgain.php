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
 * Class SentAgain
 * @package Mageplaza\AbandonedCart\Controller\Adminhtml\Index
 */
class SentAgain extends AbandonedCart
{
    /**
     * Sent again abandoned cart email action
     *
     * @return void
     */
    public function execute()
    {
        $id  = $this->getRequest()->getParam('id');
        $log = $this->logsFactory->create()->load($id);
        if ($log->getId()) {
            try {
                $this->abandonedCartModel->sendAgain($log);
                $this->messageManager->addSuccessMessage(__('Email send successfully.'));
            } catch (Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('There is an error occur when sending email. Please try again later.'));
            }

            $log->setSequentNumber($log->getSequentNumber() + 1)->save();
        }

        $this->_redirect('abandonedcart/*/report');
    }
}
