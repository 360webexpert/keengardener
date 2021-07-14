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

namespace Mageplaza\AbandonedCart\Block\Email;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\Store;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Config;
use Mageplaza\AbandonedCart\Helper\Data as ModuleHelper;

/**
 * Class Template
 * @package Mageplaza\AbandonedCart\Block\Email
 */
class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var PriceCurrency
     */
    protected $priceCurrency;

    /**
     * @var ModuleHelper
     */
    protected $helperData;

    /**
     * @var Data
     */
    protected $taxHelper;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Template constructor.
     *
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrency $priceCurrency
     * @param ModuleHelper $helperData
     * @param QuoteFactory $quoteFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        PriceCurrency $priceCurrency,
        ModuleHelper $helperData,
        QuoteFactory $quoteFactory,
        array $data = []
    ) {
        $this->_productRepository = $productRepository;
        $this->imageHelper        = $context->getImageHelper();
        $this->priceCurrency      = $priceCurrency;
        $this->helperData         = $helperData;
        $this->taxHelper          = $context->getTaxData();
        $this->quoteFactory       = $quoteFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return Quote|null
     */
    public function getQuote()
    {
        if ($quoteId = $this->getQuoteId()) {
            return $this->quoteFactory->create()->load($quoteId);
        }

        return null;
    }

    /**
     * Get items in quote
     *
     * @return Item[]
     */
    public function getProductCollection()
    {
        $items = [];

        if ($quote = $this->getQuote()) {
            return $quote->getAllVisibleItems();
        }

        return $items;
    }

    /**
     * Get subtotal in quote
     *
     * @param bool $inclTax
     *
     * @return float|string
     */
    public function getSubtotal($inclTax = false)
    {
        $subtotal = 0;
        if ($quote = $this->getQuote()) {
            $address  = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
            $subtotal = $inclTax ? $address->getSubtotalInclTax() : $address->getSubtotal();
        }

        return $this->priceCurrency->format(
            $subtotal,
            true,
            PriceCurrency::DEFAULT_PRECISION,
            $quote ? $quote->getStoreId() : null
        );
    }

    /**
     * Get image url in quote
     *
     * @param Item $_item
     *
     * @return string
     */
    public function getProductImage($_item)
    {
        $productId = $_item->getProductId();
        try {
            /** @var Product $product */
            $product = $this->_productRepository->getById($productId);
            /** @var Store $store */
            $store    = $this->_storeManager->getStore();
            $imageUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

            return str_replace('\\', '/', $imageUrl);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get item price in quote
     *
     * @param $_item
     *
     * @return float|string
     */
    public function getProductPrice($_item)
    {
        $productPrice = $_item->getRowTotal();

        return $this->priceCurrency->format($productPrice, false);
    }

    /**
     * @return string
     */
    public function getPlaceholderImage()
    {
        return $this->imageHelper->getDefaultPlaceholderUrl('image');
    }

    /**
     * @return Config
     */
    public function getTaxConfig()
    {
        return $this->taxHelper->getConfig();
    }
}
