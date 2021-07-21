<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Ebizmarts\SagePaySuite\Controller\Server;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Psr\Log\LoggerInterface;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;

class Cancel extends Action
{
    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /** @var Config */
    private $config;

    /** @var LoggerInterface */
    private $logger;

    /** @var Session */
    private $checkoutSession;

    /** @var Quote */
    private $quote;

    /** @var QuoteIdMaskFactory */
    private $quoteIdMaskFactory;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var RecoverCart */
    private $recoverCart;
    
    /**
     * Cancel constructor.
     * @param Context $context
     * @param Logger $suiteLogger
     * @param Config $config
     * @param LoggerInterface $logger
     * @param Session $checkoutSession
     * @param Quote $quote
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param EncryptorInterface $encryptor
     * @param RecoverCart $recoverCart
     */
    public function __construct(
        Context $context,
        Logger $suiteLogger,
        Config $config,
        LoggerInterface $logger,
        Session $checkoutSession,
        Quote $quote,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        EncryptorInterface $encryptor,
        RecoverCart $recoverCart
    ) {
    
        parent::__construct($context);
        $this->suiteLogger        = $suiteLogger;
        $this->config             = $config;
        $this->logger             = $logger;
        $this->checkoutSession    = $checkoutSession;
        $this->quote              = $quote;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->encryptor          = $encryptor;
        $this->recoverCart        = $recoverCart;

        $this->config->setMethodCode(Config::METHOD_SERVER);
    }

    public function execute()
    {
        $this->saveErrorMessage();

        $storeId = $this->getRequest()->getParam("_store");
        $quoteId = $this->encryptor->decrypt($this->getRequest()->getParam("quote"));

        $this->quote->setStoreId($storeId);
        $this->quote->load($quoteId);

        if (empty($this->quote->getId())) {
            throw new \Exception("Quote not found.");
        }

        $this->recoverCart->setShouldCancelOrder(true)->execute();

        $this
            ->getResponse()
            ->setBody(
                '<script>window.top.location.href = "'
                . $this->_url->getUrl('checkout/', [
                    '_secure' => true,
                ])
                . '";</script>'
            );
    }

    private function saveErrorMessage()
    {
        $message = $this->getRequest()->getParam("message");
        if (!empty($message)) {
            $this->messageManager->addError($message);
        }
    }

    /**
     * @param Quote $quote
     */
    private function inactivateQuote($quote)
    {
        $quote->setIsActive(0);
        $quote->save();
    }
}
