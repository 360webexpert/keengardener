<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Helper;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use \Magento\Checkout\Model\Session as CheckoutSession;

class Checkout extends AbstractHelper
{

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Checkout data
     * @var Data
     */
    private $checkoutData;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @param Context $context
     * @param QuoteManagement $quoteManagement
     * @param OrderSender $orderSender
     * @param Session $customerSession
     * @param Data $checkoutData
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param Copy $objectCopyService
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Logger $suiteLogger
     */
    public function __construct(
        Context $context,
        QuoteManagement $quoteManagement,
        OrderSender $orderSender,
        Session $customerSession,
        CheckoutHelper $checkoutData,
        DataObjectHelper $dataObjectHelper,
        CustomerRepositoryInterface $customerRepository,
        Copy $objectCopyService,
        CheckoutSession $checkoutSession,
        Logger $suiteLogger
    ) {
    
        parent::__construct($context);
        $this->quoteManagement    = $quoteManagement;
        $this->orderSender        = $orderSender;
        $this->customerSession    = $customerSession;
        $this->checkoutData       = $checkoutData;
        $this->objectCopyService  = $objectCopyService;
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper   = $dataObjectHelper;
        $this->checkoutSession    = $checkoutSession;
        $this->suiteLogger        = $suiteLogger;
        $this->quote              = $this->checkoutSession->getQuote();
    }

    /**
     * Place order manually from default checkout
     *
     * @return \Magento\Sales\Model\Order
     * @param $quote \Magento\Quote\Api\Data\CartInterface
     * @throws LocalizedException
     */
    public function placeOrder($quote = null)
    {

        switch ($this->getCheckoutMethod()) {
            case Onepage::METHOD_GUEST:
                $this->prepareGuestQuote();
                break;
            case Onepage::METHOD_REGISTER:
                $this->prepareNewCustomerQuote();
                break;
            default:
                break;
        }

        $this->quote->collectTotals();

        $order = $this->quoteManagement->submit((null === $quote ? $this->quote : $quote));

        if (!$order) {
            throw new LocalizedException(
                __('Can not save order. Please try another payment option.')
            );
        }

        return $order;
    }

    /**
     * Get checkout method
     *
     * @return string
     */
    private function getCheckoutMethod()
    {
        if ($this->customerSession->isLoggedIn()) {
            return Onepage::METHOD_CUSTOMER;
        }
        if (!$this->quote->getCheckoutMethod()) {
            if ($this->checkoutData->isAllowedGuestCheckout($this->quote)) {
                $this->quote->setCheckoutMethod(Onepage::METHOD_GUEST);
            } else {
                $this->quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
            }
        }
        return $this->quote->getCheckoutMethod();
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @return $this
     */
    private function prepareGuestQuote()
    {
        $quote = $this->quote;

        $quote->setCustomerId(null);
        $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
        $quote->setCustomerFirstname($quote->getBillingAddress()->getFirstname());
        $quote->setCustomerLastname($quote->getBillingAddress()->getLastname());
        $quote->setCustomerIsGuest(true);
        $quote->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);

        return $this;
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @return void
     */
    private function prepareNewCustomerQuote()
    {
        $quote = $this->quote;
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $quote->getCustomer();
        $customerBillingData = $billing->exportCustomerAddress();
        $dataArray = $this->objectCopyService->getDataFromFieldset('checkout_onepage_quote', 'to_customer', $quote);
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $dataArray,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $quote->setCustomer($customer);
        $quote->setCustomerId(true);

        $customerBillingData->setIsDefaultBilling(true);

        if ($shipping) {
            if (!$shipping->getSameAsBilling()) {
                $customerShippingData = $shipping->exportCustomerAddress();
                $customerShippingData->setIsDefaultShipping(true);
                $shipping->setCustomerAddressData($customerShippingData);
                // Add shipping address to quote since customer Data Object does not hold address information
                $quote->addCustomerAddress($customerShippingData);
            } else {
                $shipping->setCustomerAddressData($customerBillingData);
                $customerBillingData->setIsDefaultShipping(true);
            }
        } else {
            $customerBillingData->setIsDefaultShipping(true);
        }
        $billing->setCustomerAddressData($customerBillingData);

        // Add billing address to quote since customer Data Object does not hold address information
        $quote->addCustomerAddress($customerBillingData);
    }

    /**
     * @param $order
     */
    public function sendOrderEmail($order)
    {
        $this->orderSender->send($order);
    }
}
