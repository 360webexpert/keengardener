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

namespace Mageplaza\FreeGifts\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Downloadable\Model\Product\Type as TypeDownloadable;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection as QuoteItemCollection;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\FreeGifts\Block\Adminhtml\Rule\Gift\Listing;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\RuleFactory;

/**
 * Class Gift
 * @package Mageplaza\FreeGifts\Helper
 */
class Gift
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var QuoteItemFactory
     */
    protected $_quoteItemFactory;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var TypeConfigurable
     */
    protected $_typeConfigurable;

    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var ImageHelper
     */
    protected $_imageHelper;

    /**
     * @var Emulation
     */
    protected $_appEmulation;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Gift constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param QuoteItemFactory $quoteItemFactory
     * @param CheckoutSession $checkoutSession
     * @param TypeConfigurable $typeConfigurable
     * @param ImageHelper $imageHelper
     * @param RuleFactory $ruleFactory
     * @param Emulation $appEmulation
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        QuoteItemFactory $quoteItemFactory,
        CheckoutSession $checkoutSession,
        TypeConfigurable $typeConfigurable,
        ImageHelper $imageHelper,
        RuleFactory $ruleFactory,
        Emulation $appEmulation,
        StoreManagerInterface $storeManager
    ) {
        $this->_productRepository = $productRepository;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_typeConfigurable = $typeConfigurable;
        $this->_ruleFactory = $ruleFactory;
        $this->_imageHelper = $imageHelper;
        $this->_appEmulation = $appEmulation;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param int $productId
     *
     * @return ProductModel
     * @throws NoSuchEntityException
     */
    public function getProductById($productId)
    {
        /** @var ProductModel $product */
        $product = $this->_productRepository->getById($productId);

        return $product;
    }

    /**
     * @param int $giftId
     * @param null $quoteId
     *
     * @return bool
     */
    public function isGiftAdded($giftId, $quoteId = null)
    {
        $quoteItem = $this->getCurrentQuoteItems($quoteId);
        $quoteItem->addFieldToFilter('product_id', $giftId)
            ->addFieldToFilter(HelperRule::QUOTE_RULE_ID, ['neq' => 'NULL']);

        if ((bool)$quoteItem->getSize()) {
            /** @var QuoteItem $addedGift */
            $addedGift = $quoteItem->getFirstItem();
            if ($addedOptions = $addedGift->getBuyRequest()->getDataByKey('super_attribute')) {
                return $addedOptions;
            }

            return true;
        }

        return false;
    }

    /**
     * @param string|int $ruleId
     * @param null $quoteId
     *
     * @return bool
     */
    public function isMaxGift($ruleId, $quoteId = null)
    {
        $quoteItem = $this->getCurrentQuoteItems($quoteId);
        $ruleModel = $this->_ruleFactory->create();
        $rule = $ruleModel->load($ruleId);
        $quoteItem->addFieldToFilter(HelperRule::QUOTE_RULE_ID, $ruleId)
            ->addFieldToFilter('parent_item_id', ['null' => true]);

        return $quoteItem->getSize() >= $rule->getMaxGift();
    }

    /**
     * @param null $quoteId
     *
     * @return QuoteItemCollection
     */
    public function getCurrentQuoteItems($quoteId = null)
    {
        $quoteItem = $this->_quoteItemFactory->create();
        $quoteId = $quoteId === null ? $this->_checkoutSession->getQuoteId() : $quoteId;

        return $quoteItem->addFieldToFilter('quote_id', $quoteId);
    }

    /**
     * @param $giftId
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isGiftInStock($giftId)
    {
        $product = $this->getProductById($giftId);
        if ($product->getTypeId() === TypeConfigurable::TYPE_CODE && $this->getFirstConfigVariant($product)) {
            return true;
        }

        return $product->isSalable() && $product->isInStock();
    }

    /**
     * @param ProductModel $product
     *
     * @return bool|ProductModel|mixed
     */
    public function getFirstConfigVariant($product)
    {
        /** @var TypeConfigurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $children = $typeInstance->getUsedProducts($product);

        /** @var ProductModel $child */
        foreach ($children as $child) {
            if ($child->isSalable() && $child->isInStock()) {
                return $child;
            }
        }

        return false;
    }

    /**
     * @param ProductModel $configProduct
     *
     * @return array
     */
    public function getConfigAttributes($configProduct)
    {
        /**@var TypeConfigurable $typeInstance */
        $typeInstance = $configProduct->getTypeInstance();
        $attributes = $typeInstance->getConfigurableAttributes($configProduct);
        $configAttributes = [];
        foreach ($attributes as $attribute) {
            $configAttributes[$attribute->getAttributeId()] = $attribute->getProductAttribute()->getAttributeCode();
        }

        return $configAttributes;
    }

    /**
     * @param ProductModel $productGift
     *
     * @return array
     */
    public function getGiftOptions($productGift)
    {
        $giftOptions = [];
        $options = $this->_typeConfigurable->getConfigurableAttributesAsArray($productGift);
        foreach ($options as $option) {
            $giftOptions[] = [
                'label' => $option['frontend_label'],
                'attribute_id' => $option['attribute_id'],
                'values' => $this->filterGiftOptionValues($option['values']),
            ];
        }

        return $giftOptions;
    }

    /**
     * @param array $optionValues
     *
     * @return array
     */
    public function filterGiftOptionValues($optionValues)
    {
        foreach ($optionValues as $key => $optionValue) {
            if (isset($optionValue['label'])) {
                unset($optionValues[$key]['label']);
            }
            if (isset($optionValue['product_super_attribute_id'])) {
                unset($optionValues[$key]['product_super_attribute_id']);
            }
            if (isset($optionValue['default_label'])) {
                unset($optionValues[$key]['default_label']);
            }
            if (isset($optionValue['use_default_value'])) {
                unset($optionValues[$key]['use_default_value']);
            }
        }

        return $optionValues;
    }

    /**
     * @param array $attributes
     * @param ProductModel $product
     *
     * @return ProductModel|null
     */
    public function getProductByAttributes($attributes, $product)
    {
        return $this->_typeConfigurable->getProductByAttributes($attributes, $product);
    }

    /**
     * @param string $discountType
     * @param float $giftPrice
     * @param string $productPrice
     *
     * @return float
     */
    public function getGiftPrice($discountType, $giftPrice, $productPrice)
    {
        return $discountType === Listing::TYPE_PERCENT ? $productPrice / 100 * $giftPrice : (float)$giftPrice;
    }

    /**
     * @param ProductModel $gift
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getGiftImage($gift)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        $imageUrl = $this->_imageHelper->init($gift, 'product_page_main_image')->getUrl();
        $this->_appEmulation->stopEnvironmentEmulation();

        return $imageUrl;
    }

    /**
     * @param ProductModel $gift
     *
     * @return mixed
     */
    public function requireLinks(ProductModel $gift)
    {
        /** @var TypeDownloadable $typeInstance */
        $typeInstance = $gift->getTypeInstance();
        if ($gift->getTypeId() === TypeDownloadable::TYPE_DOWNLOADABLE) {
            $requireLink = (int)$typeInstance->getLinkSelectionRequired($gift);
            if ($requireLink) {
                $linkIds = [];
                $links = $typeInstance->getLinks($gift);
                foreach ($links as $link) {
                    $linkIds[] = $link->getId();
                }

                return $linkIds;
            }
        }

        return false;
    }
}
