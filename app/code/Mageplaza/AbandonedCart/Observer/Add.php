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

namespace Mageplaza\AbandonedCart\Observer;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Psr\Log\LoggerInterface;

/**
 * Class Add
 * @package Mageplaza\AbandonedCart\Observer
 */
class Add implements ObserverInterface
{
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteResource
     */
    protected $quoteResource;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Add constructor.
     *
     * @param QuoteFactory $quoteFactory
     * @param QuoteResource $quoteResource
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteResource $quoteResource,
        Session $checkoutSession,
        LoggerInterface $logger
    ) {
        $this->quoteFactory    = $quoteFactory;
        $this->quoteResource   = $quoteResource;
        $this->checkoutSession = $checkoutSession;
        $this->logger          = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @throws AlreadyExistsException
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $quoteModel = $this->quoteFactory->create();
        $this->quoteResource->load($quoteModel, $this->checkoutSession->getQuoteId());
        if ($quoteModel->getUpdatedAt() === '0000-00-00 00:00:00') {
            $quoteModel->setUpdatedAt($quoteModel->getCreatedAt());
            try {
                $this->quoteResource->save($quoteModel);
            } catch (AlreadyExistsException $e) {
                $this->logger->critical($e->getMessage());
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
