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

namespace Mageplaza\FreeGifts\Observer;

use Exception;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Gift\Item as GiftItem;
use Zend\Uri\Uri as ZendUri;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Block\Cart\Notification;
use Magento\Framework\Message\ManagerInterface;


/**
 * Class AbstractObserver
 * @package Mageplaza\FreeGifts\Observer
 */
abstract class AbstractObserver implements ObserverInterface
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @var GiftItem
     */
    protected $_giftItem;

    /**
     * @var ZendUri
     */
    protected $_zendUri;

    /**
     * @var bool
     */
    protected $_cartAddComplete = false;

    /**
     * @var boolean
     */
    protected $_isCartAllGift;

    /**
     * @var Notification
     */
    protected $notification;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * AbstractObserver constructor.
     *
     * @param HelperRule $helperRule
     * @param Registry $registry
     * @param CheckoutSession $checkoutSession
     * @param GiftItem $giftItem
     * @param ZendUri $zendUri
     * @param Notification $notification
     * @param ManagerInterface $messageManager
     * @param HelperData $helperData
     */
    public function __construct(
        HelperRule $helperRule,
        Registry $registry,
        CheckoutSession $checkoutSession,
        GiftItem $giftItem,
        ZendUri $zendUri,
        Notification $notification,
        ManagerInterface $messageManager,
        HelperData $helperData
    ) {
        $this->_helperRule      = $helperRule;
        $this->_registry        = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->_giftItem        = $giftItem;
        $this->_zendUri         = $zendUri;
        $this->notification     = $notification;
        $this->messageManager   = $messageManager;
        $this->helperData       = $helperData;
    }

    /**
     * @param Quote $quote
     *
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->_quote = $quote;

        return $this;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function freeGift()
    {
        $validRules           = $this->_helperRule->setExtraData(false)->getAllValidRules();
        $shippingAddress      = $this->_quote->getShippingAddress();
        $this->_isCartAllGift = $this->isCartAllGift();
        if (!$this->_isCartAllGift) {
            foreach ($validRules as $validRule) {
                if ($validRule['auto_add']) {
                    $this->addGift($validRule['gifts'], (int)$validRule['rule_id'], $validRule['max_gift']);
                }
            }
        }
        if (!$shippingAddress->getCountryId()) {
            $shippingAddress->setCountryId($this->_helperRule->getHelperData()->getDefaultCountry());
        }

        $validRuleIds = array_map('intval', array_keys($validRules));
        $this->removeInvalidateItems($validRuleIds);
        $this->_quote->save();
        $this->_quote->setTotalsCollectedFlag(false);
        $this->_quote->collectTotals();
    }

    /**
     * @param array $gifts
     * @param int $ruleId
     * @param int $limit
     *
     * @throws LocalizedException
     */
    public function addGift($gifts, $ruleId, $limit)
    {
        $counter = 1;
        foreach ($gifts as $gift) {
            if ($counter <= $limit && $this->canAddGift($gift['id'], $ruleId) && !$this->isGiftExist($gift['id'])) {
                $productGift   = $this->_helperRule->getHelperGift()->getProductById($gift['id']);
                $productParams = ['product' => $gift['id'], 'qty' => 1, HelperRule::OPTION_RULE_ID => $ruleId];

                if ($productGift->getTypeId() === TypeConfigurable::TYPE_CODE) {
                    $productParams['super_attribute'] = isset($gift['super_attribute'])
                        ? $gift['super_attribute']
                        : $this->getSuperAttributes($productGift);
                }
                if ($links = $this->_helperRule->getHelperGift()->requireLinks($productGift)) {
                    $productParams['links'] = $links;
                }
                if (isset($gift['options']) && (int)$productGift->getRequiredOptions()) {
                    $productParams['options'] = $gift['options'];
                }

                $productGift->addCustomOption(HelperRule::QUOTE_RULE_ID, $ruleId);
                $this->_quote->addProduct($productGift, new DataObject($productParams));
                $counter++;
            }

            if ($this->isGiftExist($gift['id'])) {
                $counter++;
            }
        }
    }

    /**
     * @param $giftId
     * @param $ruleId
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function canAddGift($giftId, $ruleId)
    {
        $giftHelper = $this->_helperRule->getHelperGift();
        if (!$giftHelper->isGiftInStock($giftId)) {
            return false;
        }
        if ($giftHelper->isMaxGift($ruleId)) {
            return false;
        }
        if ((int)$giftHelper->getProductById($giftId)->getRequiredOptions()) {
            return false;
        }

        $deleteGifts = $this->_checkoutSession->getFreeGiftsDeleted();

        return $this->_cartAddComplete ? true : !isset($deleteGifts[$ruleId]);
    }

    /**
     * @param int $giftId
     *
     * @return bool
     */
    public function isGiftExist($giftId)
    {
        if ($this->_quote->hasProductId($giftId)) {
            $collection = $this->_helperRule->getHelperGift()->getCurrentQuoteItems($this->_quote->getId());
            $collection->addFieldToFilter('product_id', $giftId)
                ->addFieldToFilter(HelperRule::QUOTE_RULE_ID, ['neq' => 'NULL']);

            return (bool)$collection->getSize();
        }

        return false;
    }

    /**
     * @param ProductModel $configProduct
     *
     * @return array
     */
    public function getSuperAttributes($configProduct)
    {
        $superAttributes = [];
        $firstVariant    = $this->_helperRule->getHelperGift()->getFirstConfigVariant($configProduct);
        $attributes      = $this->_helperRule->getHelperGift()->getConfigAttributes($configProduct);
        foreach ($attributes as $id => $code) {
            $superAttributes[$id] = $firstVariant->getDataByKey($code);
        }

        return $superAttributes;
    }

    /**
     * @return int
     */
    public function isCartAllGift()
    {
        $itemCollection = $this->_helperRule->getHelperGift()->getCurrentQuoteItems($this->_quote->getId());
        $itemCollection->addFieldToFilter(HelperRule::QUOTE_RULE_ID, ['null' => true]);

        return $itemCollection->getSize() === 0;
    }

    /**
     * @param array $ruleIds
     */
    public function removeInvalidateItems($ruleIds)
    {
        $quoteItems    = $this->_quote->getAllVisibleItems();
        $isCartAllGift = $this->_isCartAllGift === null ? $this->isCartAllGift() : $this->_isCartAllGift;
        if ($isCartAllGift) {
            foreach ($quoteItems as $quoteItem) {
                $this->_giftItem->removeAndDelete($quoteItem);
            }
        } else {
            foreach ($quoteItems as $quoteItem) {
                if ($itemRuleId = (int)$quoteItem->getDataByKey(HelperRule::QUOTE_RULE_ID)) {
                    in_array($itemRuleId, $ruleIds, true)
                        ? $quoteItem->setQty(1)
                        : $this->_giftItem->removeAndDelete($quoteItem);
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_helperRule->getHelperData()->isEnabled();
    }
}
