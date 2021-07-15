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

namespace Mageplaza\Shopbybrand\Api\Data;

/**
 * Interface BrandPageConfigInterface
 * @package Mageplaza\Shopbybrand\Api\Data
 */
interface BrandConfigInterface
{
    /**
     * Get brand list name
     *
     * @return string
     */
    public function getBrandListName();

    /**
     * Set brand list name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setBrandListName($name);

    /**
     * Get brand list style
     *
     * @return int
     */
    public function getBrandlistStyle();

    /**
     * Set brand list style
     *
     * @param int $style
     *
     * @return $this
     */
    public function setBrandlistStyle($style);

    /**
     * Get brand list display
     *
     * @return int
     */
    public function getDisplayOption();

    /**
     * @param int $option
     *
     * @return $this
     */
    public function setDisplayOption($option);

    /**
     * Get Brand Logo Width on Brand List
     *
     * @return int
     */
    public function getBrandListLogoWidth();

    /**
     * @param int $width
     *
     * @return $this
     */
    public function setBrandListLogoWidth($width);

    /**
     * Get Brand Logo Height on Brand List
     *
     * @return int
     */
    public function getBrandListLogoHeight();

    /**
     * @param int $height
     *
     * @return $this
     */
    public function setBrandListLogoHeight($height);

    /**
     * Get Style Color
     *
     * @return string
     */
    public function getColor();

    /**
     * @param string $color
     *
     * @return $this
     */
    public function setColor($color);

    /**
     * Show Brand Short Description
     *
     * @return bool
     */
    public function getShowDescription();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setShowDescription($value);

    /**
     * Show Brand Product Quantity
     *
     * @return bool
     */
    public function getShowProductQty();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setShowProductQty($value);

    /**
     * Get custom css
     *
     * @return string
     */
    public function getCustomCss();

    /**
     * @param string $css
     *
     * @return $this
     */
    public function setCustomCss($css);

    /**
     * Show brand info in product page
     *
     * @return string
     */
    public function getShowBrandInfo();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandInfo($value);

    /**
     * Get Brand Logo Width in product page
     *
     * @return int
     */
    public function getLogoWidthOnProductPage();

    /**
     * @param int $width
     *
     * @return $this
     */
    public function setLogoWidthOnProductPage($width);

    /**
     * Get Brand Logo Height in product page
     *
     * @return int
     */
    public function getLogoHeightOnProductPage();

    /**
     * @param int $height
     *
     * @return $this
     */
    public function setLogoHeightOnProductPage($height);
}
