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
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\AbandonedCart\Block\Adminhtml\Chart\Products;
use Mageplaza\AbandonedCart\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class Report
 * @package Mageplaza\AbandonedCart\Controller\Adminhtml\Index
 */
class ProductReport extends Action
{
    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @var Products
     */
    private $productsChart;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ProductReport constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Products $productsChart
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Products $productsChart,
        LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->productsChart     = $productsChart;

        parent::__construct($context);
        $this->logger = $logger;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws Exception
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Abandoned Product Report'));
        if ($this->getRequest()->isAjax()) {
            try {
                $productsChart = $this->productsChart->getCollectionData();
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());

                return $resultPage;
            }

            return $this->getResponse()->representJson(
                Data::jsonEncode(['chart' => $productsChart])
            );
        }

        return $resultPage;
    }
}
