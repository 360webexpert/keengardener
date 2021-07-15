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
 * Class Clear
 * @package Mageplaza\AbandonedCart\Controller\Adminhtml\Index
 */
class Clear extends AbandonedCart
{
    /**
     * Clear abandoned cart email logs
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->logsFactory->create()->clear();
            $this->messageManager->addSuccessMessage(__('Clear success.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('There is an error occurs. Please try again later.'));
        }

        $this->_redirect('abandonedcart/*/report');
    }
}
