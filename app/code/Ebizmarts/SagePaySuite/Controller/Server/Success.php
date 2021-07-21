<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Server;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class Success extends \Magento\Framework\App\Action\Action
{

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var OrderLoader
     */
    private $orderLoader;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Success constructor.
     * @param Context $context
     * @param Logger $suiteLogger
     * @param LoggerInterface $logger
     * @param Session $checkoutSession
     * @param QuoteRepository $quoteRepository
     * @param EncryptorInterface $encryptor
     * @param OrderLoader $orderLoader
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Context $context,
        Logger $suiteLogger,
        LoggerInterface $logger,
        Session $checkoutSession,
        QuoteRepository $quoteRepository,
        EncryptorInterface $encryptor,
        OrderLoader $orderLoader,
        RedirectFactory $resultRedirectFactory
    ) {

        parent::__construct($context);

        $this->suiteLogger     = $suiteLogger;
        $this->logger          = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->encryptor        = $encryptor;
        $this->orderLoader      = $orderLoader;
        $this->resultRedirectFactory   =  $resultRedirectFactory;
    }

    public function execute()
    {
        try {
            $request = $this->getRequest();

            $storeId = $request->getParam("_store");
            $quoteId = $this->encryptor->decrypt($request->getParam("quoteid"));

            $quote = $this->quoteRepository->get($quoteId, array($storeId));

            $order = $this->orderLoader->loadOrderFromQuote($quote);

            //prepare session to success page
            $this->checkoutSession->clearHelperData();
            $this->checkoutSession->setLastQuoteId($quote->getId());
            $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
            $this->checkoutSession->setLastOrderId($order->getEntityId());
            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->checkoutSession->setLastOrderStatus($order->getStatus());

            //remove order pre-saved flag from checkout
            $this->checkoutSession->setData(\Ebizmarts\SagePaySuite\Model\Session::PRESAVED_PENDING_ORDER_KEY, null);
            $this->checkoutSession->setData(\Ebizmarts\SagePaySuite\Model\Session::CONVERTING_QUOTE_TO_ORDER, 0);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addError(__('An error ocurred.'));
        }

        return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success', ['_secure' => true]);
    }
}
