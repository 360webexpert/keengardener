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
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\AbandonedCart\Helper\Data;
use Mageplaza\AbandonedCart\Model\CartBoard as CartBoardModel;
use Psr\Log\LoggerInterface;

/**
 * Class Report
 * @package Mageplaza\AbandonedCart\Controller\Adminhtml\Index
 */
class CartBoard extends Action
{
    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @var CartBoardModel
     */
    private $cartBoard;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CartBoard constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CartBoardModel $cartBoard
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CartBoardModel $cartBoard,
        LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->cartBoard         = $cartBoard;
        $this->logger            = $logger;

        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Cart Board'));

        if ($this->getRequest()->isAjax()) {
            try {
                $cartBoardData = $this->cartBoard->getData();

                return $this->getResponse()->representJson(
                    Data::jsonEncode(['data' => $cartBoardData])
                );
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $resultPage;
    }
}
