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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Model;

use Magento\Framework\Api\AbstractSimpleObject;
use Mageplaza\Shopbybrand\Api\Data\BrandConfigInterface;

/**
 * Class BrandConfig
 * @package Mageplaza\Shopbybrand\Model
 */
class BrandConfig extends AbstractSimpleObject implements BrandConfigInterface
{
    const NAME = 'name';
    const BRANDLIST_STYLE = 'brandlist_style';
    const DISPLAY = 'display';
    const BRAND_LOGO_WIDTH = 'brand_logo_width';
    const BRAND_LOGO_HEIGHT = 'brand_logo_height';
    const COLOR = 'color';
    const SHOW_DESCRIPTION = 'show_description';
    const SHOW_PRODUCT_QTY = 'show_product_qty';
    const CUSTOM_CSS = 'custom_css';
    const SHOW_BRAND_INFO = 'show_brand_info';
    const LOGO_WIDTH_ON_PRODUCT_PAGE = 'logo_width_on_product_page';
    const LOGO_HEIGHT_ON_PRODUCT_PAGE = 'logo_height_on_product_page';

    /**
     * @inheritdoc
     */
    public function getBrandListName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function getBrandListLogoWidth()
    {
        return $this->_get(self::BRAND_LOGO_WIDTH);
    }

    /**
     * @inheritdoc
     */
    public function getBrandListLogoHeight()
    {
        return $this->_get(self::BRAND_LOGO_HEIGHT);
    }

    /**
     * @inheritdoc
     */
    public function getBrandlistStyle()
    {
        return $this->_get(self::BRANDLIST_STYLE);
    }

    /**
     * @inheritdoc
     */
    public function getColor()
    {
        return $this->_get(self::COLOR);
    }

    /**
     * @inheritdoc
     */
    public function getCustomCss()
    {
        return $this->_get(self::CUSTOM_CSS);
    }

    /**
     * @inheritdoc
     */
    public function getDisplayOption()
    {
        return $this->_get(self::DISPLAY);
    }

    /**
     * @inheritdoc
     */
    public function getLogoHeightOnProductPage()
    {
        return $this->_get(self::LOGO_HEIGHT_ON_PRODUCT_PAGE);
    }

    /**
     * @inheritdoc
     */
    public function getLogoWidthOnProductPage()
    {
        return $this->_get(self::LOGO_WIDTH_ON_PRODUCT_PAGE);
    }

    /**
     * @inheritdoc
     */
    public function getShowBrandInfo()
    {
        return $this->_get(self::SHOW_BRAND_INFO);
    }

    /**
     * @inheritdoc
     */
    public function getShowDescription()
    {
        return $this->_get(self::SHOW_DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function getShowProductQty()
    {
        return $this->_get(self::SHOW_PRODUCT_QTY);
    }

    /**
     * @inheritdoc
     */
    public function setBrandListLogoHeight($height)
    {
        return $this->setData(self::BRAND_LOGO_HEIGHT, $height);
    }

    /**
     * @inheritdoc
     */
    public function setBrandListLogoWidth($width)
    {
        return $this->setData(self::BRAND_LOGO_WIDTH, $width);
    }

    /**
     * @inheritdoc
     */
    public function setBrandListName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function setBrandlistStyle($style)
    {
        return $this->setData(self::BRANDLIST_STYLE, $style);
    }

    /**
     * @inheritdoc
     */
    public function setColor($color)
    {
        return $this->setData(self::COLOR, $color);
    }

    /**
     * @inheritdoc
     */
    public function setCustomCss($css)
    {
        return $this->setData(self::CUSTOM_CSS, $css);
    }

    /**
     * @inheritdoc
     */
    public function setDisplayOption($option)
    {
        return $this->setData(self::DISPLAY, $option);
    }

    /**
     * @inheritdoc
     */
    public function setLogoHeightOnProductPage($height)
    {
        return $this->setData(self::LOGO_HEIGHT_ON_PRODUCT_PAGE, $height);
    }

    /**
     * @inheritdoc
     */
    public function setLogoWidthOnProductPage($width)
    {
        return $this->setData(self::LOGO_WIDTH_ON_PRODUCT_PAGE, $width);
    }

    /**
     * @inheritdoc
     */
    public function setShowBrandInfo($value)
    {
        return $this->setData(self::SHOW_BRAND_INFO, $value);
    }

    /**
     * @inheritdoc
     */
    public function setShowDescription($value)
    {
        return $this->setData(self::SHOW_DESCRIPTION, $value);
    }

    /**
     * @inheritdoc
     */
    public function setShowProductQty($value)
    {
        return $this->setData(self::SHOW_PRODUCT_QTY, $value);
    }
}
