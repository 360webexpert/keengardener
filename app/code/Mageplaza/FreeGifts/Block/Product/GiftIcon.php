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

namespace Mageplaza\FreeGifts\Block\Product;

use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ResourceModel;
use Magento\Catalog\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\FreeGifts\Helper\Data;
use Mageplaza\FreeGifts\Helper\Rule;
use Mageplaza\FreeGifts\Model\Source\Apply;

/**
 * Class GiftIcon
 * @package Mageplaza\FreeGifts\Block\Product
 */
class GiftIcon extends Template
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var Gallery
     */
    protected $_gallery;

    /**
     * @var Rule
     */
    protected $_helperRule;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var ResourceModel
     */
    protected $_resourceModel;

    /**
     * @var Product
     */
    protected $currentProduct;

    /**
     * GiftIcon constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param Gallery $gallery
     * @param Rule $helperRule
     * @param Session $catalogSession
     * @param ProductFactory $productFactory
     * @param ResourceModel $resourceModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Gallery $gallery,
        Rule $helperRule,
        Session $catalogSession,
        ProductFactory $productFactory,
        ResourceModel $resourceModel,
        array $data = []
    ) {
        $this->_helperData     = $helperData;
        $this->_gallery        = $gallery;
        $this->_helperRule     = $helperRule;
        $this->_catalogSession = $catalogSession;
        $this->_productFactory = $productFactory;
        $this->_resourceModel  = $resourceModel;

        parent::__construct($context, $data);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getGiftIcon()
    {
        $storeId = $this->getStoreId();

        return $this->_helperData->getGiftIcon($storeId);
    }

    /**
     * Get width image product
     *
     * @return string
     */
    public function getGalleryWidth()
    {
        return $this->_gallery->getImageAttribute('product_page_image_medium', 'width');
    }

    /**
     * Get height image product
     *
     * @return string
     */

    /**
     * @return int|string
     */
    public function getGalleryHeight()
    {
        return $this->_gallery->getImageAttribute('product_page_image_medium', 'height');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getGalleryData()
    {
        $size = getimagesize($this->getGiftIcon());
        list($iconWidth, $iconHeight) = $size;

        return Data::jsonEncode([
            'iconWidth'     => $iconWidth,
            'iconHeight'    => $iconHeight,
            'galleryWidth'  => $this->getGalleryWidth(),
            'galleryHeight' => $this->getGalleryHeight()
        ]);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getValidItemRules()
    {
        return $this->_helperRule->setQuote($this->_helperRule->getQuote())
            ->setProduct($this->getProduct())
            ->setApply(Apply::ITEM)
            ->getValidatedRules();
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->_catalogSession->getData('last_viewed_product_id');
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->currentProduct) {
            $product = $this->_productFactory->create();
            $this->_resourceModel->load($product, $this->getProductId());

            $this->currentProduct = $product;
        }

        return $this->currentProduct;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isShowIcon()
    {
        return $this->_helperData->isEnabled() && $this->getGiftIcon() && !empty($this->getValidItemRules());
    }
}