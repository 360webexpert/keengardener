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

namespace Mageplaza\FreeGifts\Block\Checkout;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Filter\DataObject\GridFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Multishipping\Block\Checkout\Addresses;
use Magento\Multishipping\Model\Checkout\Type\Multishipping as CheckoutShipping;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\ItemFactory as QuoteItemFactory;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class MultiShipping
 * @package Mageplaza\FreeGifts\Block\Checkout
 */
class MultiShipping extends Addresses
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_FreeGifts::checkout/multi_shipping.phtml';

    /**
     * @var QuoteItemFactory
     */
    protected $_quoteItemFactory;

    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * MultiShipping constructor.
     *
     * @param Context $context
     * @param GridFactory $filterGridFactory
     * @param CheckoutShipping $multishipping
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressConfig $addressConfig
     * @param Mapper $addressMapper
     * @param QuoteItemFactory $quoteItemFactory
     * @param HelperRule $helperRule
     * @param array $data
     */
    public function __construct(
        Context $context,
        GridFactory $filterGridFactory,
        CheckoutShipping $multishipping,
        CustomerRepositoryInterface $customerRepository,
        AddressConfig $addressConfig,
        Mapper $addressMapper,
        QuoteItemFactory $quoteItemFactory,
        HelperRule $helperRule,
        array $data = []
    ) {
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->_helperRule = $helperRule;

        parent::__construct(
            $context,
            $filterGridFactory,
            $multishipping,
            $customerRepository,
            $addressConfig,
            $addressMapper,
            $data
        );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_helperRule->getHelperData()->isEnabled();
    }

    /**
     * @return string
     */
    public function getItemIds()
    {
        $itemIds = [];
        $addressItems = $this->getItems();
        $quoteItemFactory = $this->_quoteItemFactory->create();
        foreach ($addressItems as $index => $addressItem) {
            /** @var AddressItem $addressItem */
            $quoteItemId = $addressItem->getQuoteItemId();
            if ((int)$quoteItemFactory->load($quoteItemId)->getDataByKey(HelperRule::QUOTE_RULE_ID)) {
                $itemIds[] = $index . '-' . $quoteItemId;
            }
        }

        return HelperData::jsonEncode($itemIds);
    }
}
