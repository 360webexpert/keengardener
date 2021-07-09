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
 * Class Ajax
 * @package Mageplaza\AbandonedCart\Controller\Adminhtml\Index
 */
class Ajax extends AbandonedCart
{
    /**
     * Response ajax data
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $fromDate   = $this->getRequest()->getParam('from');
        $toDate     = $this->getRequest()->getParam('to');
        $result     = ['status' => false];
        try {
            if ($fromDate && $toDate && strtotime($fromDate) <= strtotime($toDate)) {
                $data       = $this->abandonedCartLog->loadReportData(
                    $fromDate,
                    $toDate,
                    $this->getRequest()->getParam('dimension')
                );
                $reportData = $this->jsonHelper->jsonEncode($data);
                $html       = $resultPage->getLayout()
                    ->createBlock('Magento\Backend\Block\Template')
                    ->setTemplate('Mageplaza_AbandonedCart::report/content.phtml')
                    ->setReportData($reportData)
                    ->toHtml();

                $result = [
                    'status'  => true,
                    'content' => $html
                ];
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }

        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($result)
        );
    }
}
