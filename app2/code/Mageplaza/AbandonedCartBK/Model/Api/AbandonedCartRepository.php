<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\AbandonedCart\Model\Api;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Mageplaza\AbandonedCart\Api\AbandonedCartRepositoryInterface;
use Mageplaza\AbandonedCart\Helper\Data;
use Mageplaza\AbandonedCart\Model\Logs;
use Mageplaza\AbandonedCart\Model\LogsFactory;
use Mageplaza\AbandonedCart\Model\Token;

/**
 * Class AbandonedCartRepository
 * @package Mageplaza\AbandonedCart\Model\Api
 */
class AbandonedCartRepository implements AbandonedCartRepositoryInterface
{
    /**
     * @var Token
     */
    protected $tokenModel;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var LogsFactory
     */
    protected $logsFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    protected $message;

    /**
     * AbandonedCart constructor.
     *
     * @param QuoteFactory $quoteFactory
     * @param Token $tokenModel
     * @param LogsFactory $logsFactory
     * @param Data $helperData
     * @param CheckoutSession $checkoutSession
     * @param Session $customerSession
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        Token $tokenModel,
        LogsFactory $logsFactory,
        Data $helperData,
        CheckoutSession $checkoutSession,
        Session $customerSession
    ) {
        $this->tokenModel      = $tokenModel;
        $this->quoteFactory    = $quoteFactory;
        $this->logsFactory     = $logsFactory;
        $this->helperData      = $helperData;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function recover($token, $quoteId, $isController = false)
    {
        /** @var Quote $quote */
        $quote = $this->quoteFactory->create()->load($quoteId);

        if (($token !== 'test_email') && !$this->helperData->isEnabled($quote->getStoreId())) {
            throw new LocalizedException(__('AbandonedCart extension is disabled.'));
        }

        if (($token !== 'test_email') && !$this->tokenModel->validateCartLink($quoteId, $token)) {
            throw new LocalizedException(__('The link is not available for your use'));
        }

        /** @var Logs $logs */
        $logs = $this->logsFactory->create();

        if (!$quote->getId() || !$quote->getIsActive()) {
            throw new LocalizedException(__('An error occurred while recovering your cart.'));
        }

        $customerId = (int) $quote->getCustomerId();

        if (!$customerId) {
            $this->checkoutSession->setQuoteId($quoteId);
            $logs->updateRecovery($quoteId);

            return true;
        }
        if ($isController) {
            if (!$this->customerSession->isLoggedIn()) {
                if (!$this->customerSession->loginById($customerId)) {
                    throw new LocalizedException(__('An error occurred while logging in your account. Please try to log in again.'));
                }

                $this->customerSession->regenerateId();
            } elseif ((int) $this->customerSession->getId() !== $customerId) {
                $this->message = __('Please login with %1', $quote->getCustomerEmail());

                return false;
            }
        }

        $logs->updateRecovery($quoteId);

        return true;
    }

    /**
     * @return string|null
     */
    public function getNoticeMessage()
    {
        return $this->message;
    }
}
