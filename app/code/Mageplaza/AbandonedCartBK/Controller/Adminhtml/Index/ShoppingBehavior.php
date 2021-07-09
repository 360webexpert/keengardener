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
use Mageplaza\AbandonedCart\Helper\Data;
use Mageplaza\AbandonedCart\Model\Behavior;
use Psr\Log\LoggerInterface;

/**
 * Class Report
 * @package Mageplaza\AbandonedCart\Controller\Adminhtml\Index
 */
class ShoppingBehavior extends Action
{
    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @var Behavior
     */
    private $behavior;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * ShoppingBehavior constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Behavior $behavior
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Behavior $behavior,
        LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->behavior          = $behavior;
        $this->logger            = $logger;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws Exception
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Shopping Behavior Analysis'));

        if ($this->getRequest()->isAjax()) {
            try {
                $behaviorChart = $this->behavior->getData();

                return $this->getResponse()->representJson(
                    Data::jsonEncode(['data' => $behaviorChart])
                );
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $resultPage;
    }
}
