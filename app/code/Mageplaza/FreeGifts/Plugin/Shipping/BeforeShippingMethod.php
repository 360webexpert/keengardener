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
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Plugin\Shipping;

use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\ShippingMethodManagement;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Source\Apply;
use Mageplaza\FreeGifts\Observer\ScreenFreeGift;
use Mageplaza\FreeGifts\Plugin\AbstractPlugin;

/**
 * Class BeforeShippingMethod
 * @package Mageplaza\FreeGifts\Plugin
 */
class BeforeShippingMethod extends AbstractPlugin
{
    /**
     * @var AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var ScreenFreeGift
     */
    protected $_screenFreeGift;

    /**
     * @var QuoteAddress
     */
    protected $_quoteAddress;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * BeforeShippingMethod constructor.
     *
     * @param HelperRule $helperRule
     * @param AddressRepositoryInterface $addressRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param ScreenFreeGift $screenFreeGift
     * @param QuoteAddress $quoteAddress
     * @param RequestInterface $request
     */
    public function __construct(
        HelperRule $helperRule,
        AddressRepositoryInterface $addressRepository,
        CartRepositoryInterface $quoteRepository,
        ScreenFreeGift $screenFreeGift,
        QuoteAddress $quoteAddress,
        RequestInterface $request
    ) {
        $this->_addressRepository = $addressRepository;
        $this->_quoteRepository = $quoteRepository;
        $this->_screenFreeGift = $screenFreeGift;
        $this->_quoteAddress = $quoteAddress;
        $this->_request = $request;

        parent::__construct($helperRule);
    }

    /**
     * @param ShippingMethodManagement $subject
     * @param $cartId
     * @param EstimateAddressInterface $address
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings("Unused")
     */
    public function beforeEstimateByAddress(
        ShippingMethodManagement $subject,
        $cartId,
        EstimateAddressInterface $address
    ) {
        if ($this->isEnabled()) {
            $this->checkFreeGift($cartId, $address);
        }

        return [$cartId, $address];
    }

    /**
     * @param ShippingMethodManagement $subject
     * @param $cartId
     * @param AddressInterface $address
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings("Unused")
     */
    public function beforeEstimateByExtendedAddress(
        ShippingMethodManagement $subject,
        $cartId,
        AddressInterface $address
    ) {
        if ($this->isEnabled() && strpos($this->_request->getServer('HTTP_REFERER'), 'checkout/cart') === false) {
            $this->checkFreeGift($cartId, $address);
        }

        return [$cartId, $address];
    }

    /**
     * @param ShippingMethodManagement $subject
     * @param $cartId
     * @param $addressId
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings("Unused")
     */
    public function beforeEstimateByAddressId(
        ShippingMethodManagement $subject,
        $cartId,
        $addressId
    ) {
        if ($this->isEnabled()) {
            $address = $this->_addressRepository->getById($addressId);
            $this->checkFreeGift($cartId, $address);
        }

        return [$cartId, $addressId];
    }

    /**
     * @param int $cartId
     * @param AddressInterface|EstimateAddressInterface|CustomerAddress $address
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     */
    public function checkFreeGift($cartId, $address)
    {
        /** @var Quote $quote */
        $quote = $this->_quoteRepository->getActive($cartId);
        if (!$address instanceof AddressInterface) {
            $addressData = [
                AddressInterface::KEY_COUNTRY_ID => $address->getCountryId(),
                AddressInterface::KEY_POSTCODE => $address->getPostcode(),
                AddressInterface::KEY_REGION_ID => $address->getRegionId(),
                AddressInterface::KEY_REGION => $address->getRegion(),
                AddressInterface::KEY_STREET => $address->getStreet(),
                AddressInterface::KEY_CITY => $address->getCity(),
                AddressInterface::CUSTOMER_ADDRESS_ID => $address->getId()
            ];
            $address = $this->_quoteAddress->setData($addressData);
        }

        $rules = $this->_helperRule->setApply(Apply::CART)
            ->setShippingAddress($address)
            ->setExtraData(false)
            ->getValidatedRules();
        $validRules = $this->_helperRule->setExtraData(false)->getAllValidRules();
        $validRuleIds = array_map('intval', array_keys($validRules));
        $this->_screenFreeGift->setExternalQuote($quote);
        $this->_screenFreeGift->removeInvalidateItems($validRuleIds);
        foreach ($rules as $rule) {
            if ($rule['auto_add']) {
                $this->_screenFreeGift->addGift($rule['gifts'], (int)$rule['rule_id'], $rule['max_gift']);
            }
        }

        $quote->save();
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
    }
}
