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

namespace Mageplaza\FreeGifts\Model\Api;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\ProductOptionInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\ItemFactory as QuoteItemFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Mageplaza\FreeGifts\Api\Data\AddGiftItemInterface;
use Mageplaza\FreeGifts\Api\Data\DisplayConfigInterface;
use Mageplaza\FreeGifts\Api\Data\FreeGiftResponseInterface as FreeGiftResponse;
use Mageplaza\FreeGifts\Api\Data\FreeGiftResponseInterfaceFactory;
use Mageplaza\FreeGifts\Api\ProductGiftInterface;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Source\Apply;
use Mageplaza\FreeGifts\Observer\ScreenFreeGift;
use Mageplaza\FreeGifts\Helper\Data as HelperData;

/**
 * Class ProductGift
 * @package Mageplaza\FreeGifts\Model\Api
 */
class ProductGift implements ProductGiftInterface
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var QuoteItemFactory
     */
    protected $_quoteItemFactory;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $_quoteIdMask;

    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var ScreenFreeGift
     */
    protected $_screenFreeGift;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var FreeGiftResponseInterfaceFactory
     */
    protected $_freeGiftResponse;

    /**
     * @var bool
     */
    protected $_collectTotal = true;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * ProductGift constructor.
     *
     * @param HelperRule $helperRule
     * @param ProductRepositoryInterface $productRepository
     * @param QuoteItemFactory $quoteItemFactory
     * @param QuoteIdMaskFactory $quoteIdMask
     * @param QuoteFactory $quoteFactory
     * @param ScreenFreeGift $screenFreeGift
     * @param CheckoutSession $checkoutSession
     * @param FreeGiftResponseInterfaceFactory $freeGiftResponse
     * @param HelperData $helperData
     */
    public function __construct(
        HelperRule $helperRule,
        ProductRepositoryInterface $productRepository,
        QuoteItemFactory $quoteItemFactory,
        QuoteIdMaskFactory $quoteIdMask,
        QuoteFactory $quoteFactory,
        ScreenFreeGift $screenFreeGift,
        CheckoutSession $checkoutSession,
        FreeGiftResponseInterfaceFactory $freeGiftResponse,
        HelperData $helperData
    ) {
        $this->_helperRule        = $helperRule;
        $this->_productRepository = $productRepository;
        $this->_quoteItemFactory  = $quoteItemFactory;
        $this->_quoteIdMask       = $quoteIdMask;
        $this->_quoteFactory      = $quoteFactory;
        $this->_screenFreeGift    = $screenFreeGift;
        $this->_checkoutSession   = $checkoutSession;
        $this->_freeGiftResponse  = $freeGiftResponse;
        $this->helperData         = $helperData;
    }

    /**
     * @return bool
     */
    public function getCollectTotals()
    {
        return $this->_collectTotal;
    }

    /**
     * @param bool $collectTotal
     *
     * @return $this
     */
    public function setCollectTotals($collectTotal)
    {
        $this->_collectTotal = $collectTotal;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getGiftsByProductSku($sku)
    {
        try {
            $product  = $this->_productRepository->get($sku);
            $response = $this->_helperRule->setApply(Apply::ITEM)->setProduct($product)->getValidatedRules();
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e->getMessage());
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function getGiftsByQuoteItemId($itemId)
    {
        $itemModel = $this->_quoteItemFactory->create();
        $item      = $itemModel->load($itemId);
        $quote     = $this->getQuoteById($item->getQuoteId());
        $response  = $this->getErrorResponse(__('This quote item does not exist.'));

        try {
            if ($item->getId()) {
                $product  = $this->_productRepository->getById($item->getDataByKey('product_id'));
                $response = $this->_helperRule->setQuote($quote)
                    ->setApply(Apply::ITEM)
                    ->setProduct($product)
                    ->getValidatedRules();
            }
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e->getMessage());
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function getGiftsByQuoteId($cartId)
    {
        $response = $this->getErrorResponse(__('This quote does not exist.'));
        try {
            if ($quote = $this->getQuoteById($cartId)) {
                $response = $this->_helperRule->setQuote($quote)->setExtraData(true)->getAllValidRules();
            }
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e->getMessage());
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function deleteGiftByQuoteItemId($quoteId, $itemId)
    {
        $quote = $this->getQuoteById($quoteId);
        if (!$quote) {
            return $this->getErrorResponse(__('This quote does not exist.'));
        }
        $item = $quote->getItemById($itemId);
        if (!$item) {
            return $this->getErrorResponse(__('Current quote does not contain this item.'));
        }

        $response = $this->getErrorResponse(__('This item is not a free gift.'));
        if ((int)$item->getDataByKey(HelperRule::QUOTE_RULE_ID)) {
            $quote->removeItem($item->getId());
            $response = true;
        }

        try {
            $quote->save();
            $quote->setTotalsCollectedFlag(false);
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e->getMessage());
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function addGift(AddGiftItemInterface $giftItem)
    {
        $quoteId       = (int)$giftItem->getQuoteId();
        $ruleId        = (int)$giftItem->getRuleId();
        $giftId        = (int)$giftItem->getGiftId();
        $helperGift    = $this->_helperRule->getHelperGift();
        $productOption = $this->getOptionSuperAttributes($giftItem->getProductOption());
        $response      = $this->prepareFreeGiftResponse($quoteId, $ruleId, $giftId);

        $quote = $this->getQuoteById($quoteId);
        if (!$quote) {
            return $response->setMessage(__('Quote with id \'%1\' does not exist.', $quoteId));
        }
        $rule = $this->_helperRule->getRuleById($ruleId);
        if (!$rule->getId()) {
            return $response->setMessage(__('Rule with id \'%1\' does not exist.', $ruleId));
        }

        try {
            /** @var Product $gift */
            $gift = $this->_productRepository->getById($giftId);
            if (!$helperGift->isGiftInStock($giftId)) {
                return $this->getErrorResponse(__('Gift with id \'%1\' is currently out of stock.', $giftId));
            }
            $validRules = $this->_helperRule->setExtraData(false)->setQuote($quote)->getAllValidRules();
        } catch (Exception $e) {
            return $response->setMessage(__($e->getMessage()));
        }

        $validRuleIds = array_map('intval', array_column($validRules, 'rule_id'));
        $ruleGiftIds  = array_map('intval', array_keys($rule->getGiftArray()));
        if (!in_array($giftId, $ruleGiftIds, true)) {
            return $response->setMessage(__('Gift with id \'%1\' does not belong to the current rule.', $giftId));
        }
        if (!in_array($ruleId, $validRuleIds, true)) {
            return $response->setMessage(__('Rule with id \'%1\' is invalid for the current quote.', $ruleId));
        }
        if ($helperGift->isMaxGift($ruleId)) {
            return $response->setMessage(__('Maximum number of gifts added for quote with id \'%1\'.', $giftId));
        }
        if ((int)$gift->getRequiredOptions() || is_array($helperGift->requireLinks($gift))) {
            return $response->setMessage(__('Gift with id \'%1\' requires custom options.', $giftId));
        }
        if ($helperGift->isGiftAdded($giftId, $quoteId)) {
            return $response->setMessage(__('Gift with id \'%1\' is already added.', $giftId));
        }

        $giftParams = ['product' => $giftId, 'qty' => 1, HelperRule::OPTION_RULE_ID => $ruleId];
        $gift->addCustomOption(HelperRule::QUOTE_RULE_ID, $ruleId);
        if ($gift->getTypeId() === TypeConfigurable::TYPE_CODE) {
            $giftParams['super_attribute'] = count($productOption)
                ? $productOption
                : $this->_screenFreeGift->getSuperAttributes($gift);
        }

        try {
            $addedItem = $quote->addProduct($gift, new DataObject($giftParams));
            $quote->save();
        } catch (Exception $e) {
            return $response->setMessage(__($e->getMessage()));
        }

        if (is_string($addedItem)) {
            return $response->setMessage(__($addedItem));
        }

        if ($this->getCollectTotals()) {
            $quote->getShippingAddress()->unsetData('cached_items_all');
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
        }

        $deletedGifts = $this->_checkoutSession->getFreeGiftsDeleted();
        if (isset($deletedGifts[$ruleId])) {
            $deletedGifts[$ruleId] = $this->removeDeletedGift($deletedGifts[$ruleId], $giftId);
        }
        $this->_checkoutSession->setFreeGiftsDeleted($deletedGifts);

        return $response->setStatus(FreeGiftResponse::STATUS_SUCCESS)
            ->setQuoteItemId($addedItem->getItemId());
    }

    /**
     * @param $quoteId
     *
     * @return Quote|false
     */
    public function getQuoteById($quoteId)
    {
        $quoteFactory     = $this->_quoteFactory->create();
        $quoteMaskFactory = $this->_quoteIdMask->create();
        if ($quoteMaskId = $quoteMaskFactory->load($quoteId, 'masked_id')->getDataByKey('quote_id')) {
            $quoteId = $quoteMaskId;
        }
        $quote = $quoteFactory->load($quoteId);

        return $quote->getId() ? $quote : false;
    }

    /**
     * @param $message
     *
     * @return FreeGiftResponse
     */
    public function getErrorResponse($message)
    {
        $response = $this->_freeGiftResponse->create();

        return $response->setStatus(FreeGiftResponse::STATUS_ERROR)->setMessage($message);
    }

    /**
     * @param int $quoteId
     * @param int $ruleId
     * @param int $giftId
     *
     * @return FreeGiftResponse
     */
    public function prepareFreeGiftResponse($quoteId, $ruleId, $giftId)
    {
        $response = $this->_freeGiftResponse->create();

        return $response->setQuoteId($quoteId)
            ->setRuleId($ruleId)
            ->setProductGiftId($giftId)
            ->setStatus(FreeGiftResponse::STATUS_ERROR);
    }

    /**
     * @param array $gifts
     * @param int $giftId
     *
     * @return mixed
     */
    public function removeDeletedGift($gifts, $giftId)
    {
        foreach ($gifts as $index => $deleteGift) {
            if ($giftId === (int)$deleteGift) {
                unset($gifts[$index]);
            }
        }

        return $gifts;
    }

    /**
     * @param ProductOptionInterface $productOption
     *
     * @return array
     */
    public function getOptionSuperAttributes($productOption)
    {
        $superAttributes = [];
        if (!$productOption || !$productOption->getExtensionAttributes() ||
            !$productOption->getExtensionAttributes()->getConfigurableItemOptions()) {
            return $superAttributes;
        }

        $configOptions = $productOption->getExtensionAttributes()->getConfigurableItemOptions();
        foreach ($configOptions as $configOption) {
            $superAttributes[$configOption->getOptionId()] = $configOption->getOptionValue();
        }

        return $superAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $helper = $this->helperData;

        return new DataObject([
            'general' => new DataObject([
                'gift_layout'  => $helper->getGiftLayout(),
                'allow_notice' => $helper->getAllowNotice(),
                'notice'       => $helper->getNotice(),
                'icon'         => $helper->getGiftIcon(),
            ]),
            'display' => new DataObject([
                'cart_page'    => $helper->getCartPage(),
                'cart_item'    => $helper->getCartItem(),
                'product_page' => $helper->getProductPage(),
            ]),
            'design'  => new DataObject([
                'label'            => $helper->getButtonLabel(),
                'background_color' => $helper->getButtonColor(),
                'text_color'       => $helper->getTextColor(),
            ])
        ]);
    }
}
