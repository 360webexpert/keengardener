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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;

/**
 * Class AccountManagement
 * @package Mageplaza\AbandonedCart\Observer
 */
class AccountManagement implements ObserverInterface
{
    /**
     * @var QuoteFactory
     */
    private $quoteModel;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteCollection
     */
    private $quoteCollection;

    /**
     * AccountManagement constructor.
     *
     * @param QuoteFactory $quoteModel
     * @param QuoteResource $quoteResource
     * @param QuoteCollection $quoteCollection
     */
    public function __construct(
        QuoteFactory $quoteModel,
        QuoteResource $quoteResource,
        QuoteCollection $quoteCollection
    ) {
        $this->quoteModel      = $quoteModel;
        $this->quoteResource   = $quoteResource;
        $this->quoteCollection = $quoteCollection;
    }

    /**
     * @param Observer $observer
     *
     * @throws AlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        $customer        = $observer->getEvent()->getCustomer();
        $quoteCollection = $this->quoteCollection->create()->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('is_active', 1)->setPageSize(1)->getFirstItem();

        $quoteId = $quoteCollection->getId();
        if ($quoteId) {
            /** @var Quote $quote */
            $quote = $this->quoteModel->create();
            $this->quoteResource->load($quote, $quoteId);
            $quote->setMpAbandonedSetChange(!$quote->getMpAbandonedSetChange());
            $this->quoteResource->save($quote);
        }
    }
}
