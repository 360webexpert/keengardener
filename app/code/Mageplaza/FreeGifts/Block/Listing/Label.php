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
 * @package     Mageplaza_ProductLabels
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Block\Listing;

use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\FreeGifts\Helper\Data;
use Mageplaza\FreeGifts\Helper\Rule;
use Mageplaza\FreeGifts\Model\Source\Apply;

/**
 * Class Label
 * @package Mageplaza\FreeGifts\Block\Listing
 */
class Label extends Template
{
    /**
     * @var Gallery
     */
    protected $_gallery;

    /**
     * @var Rule
     */
    protected $_helperRule;

    /**
     * Label constructor.
     *
     * @param Context $context
     * @param Gallery $gallery
     * @param Rule $helperRule
     * @param array $data
     */
    public function __construct(
        Context $context,
        Gallery $gallery,
        Rule $helperRule,
        array $data = []
    ) {
        $this->_gallery    = $gallery;
        $this->_helperRule = $helperRule;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->getCartProduct();
    }

    /**
     * @return string
     */
    public function getLabelPosition()
    {
        $size = getimagesize($this->getGiftIcon());
        list($iconWidth, $iconHeight) = $size;

        $width  = $iconWidth * 100 / $this->getProductImgWidth();
        $height = $iconHeight * 100 / $this->getProductImgHeight();
        $top      = (($this->getProductImgHeight() - $iconHeight) *0 / 100) / $this->getProductImgHeight() * 100;
        $left     = (($this->getProductImgWidth() - $iconWidth) * 100 / 100) / $this->getProductImgWidth() * 100;

        return sprintf(
            'width: %s%%; height: %s%%; top: %s%%; left: %s%%;',
            $width,
            $height,
            $top,
            $left
        );
    }

    /**
     * @return string
     */
    public function getProductImgWidth()
    {
        return $this->_gallery->getImageAttribute('category_page_list', 'width', 1);
    }

    /**
     * @return string
     */
    public function getProductImgHeight()
    {
        return $this->_gallery->getImageAttribute('category_page_list', 'height', 1);
    }

    /**
     * @return mixed
     */
    public function getGiftIcon()
    {
        return $this->getHelperData()->getGiftIcon();
    }

    /**
     * @return Data
     */
    protected function getHelperData()
    {
        return $this->_helperRule->getHelperData();
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
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isShowIcon()
    {
        return $this->getHelperData()->isEnabled() && $this->getGiftIcon() && !empty($this->getValidItemRules());
    }
}
