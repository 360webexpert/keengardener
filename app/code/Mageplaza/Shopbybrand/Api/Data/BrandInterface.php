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
 * Interface BrandInterface
 * @package Mageplaza\Shopbybrand\Api\Data
 */
interface BrandInterface
{
    /**
     * Constants used as data array keys
     */
    const BRAND_ID = 'brand_id';

    const OPTION_ID = 'option_id';

    const PAGE_TITLE = 'page_title';

    const URL_KEY = 'url_key';

    const IMAGE = 'image';

    const SHORT_DESCRIPTION = 'short_description';

    const DESCRIPTION = 'description';

    const IS_FEATURED = 'is_featured';

    const STATIC_BLOCK = 'static_block';

    const META_TITLE = 'meta_title';

    const META_KEYWORDS = 'meta_keywords';

    const META_DESCRIPTION = 'meta_description';

    const LABEL = 'label';

    const VALUE = 'value';

    const SORT_ORDER = 'sort_order';

    const STORE_LABELS = 'store_labels';

    const IS_DEFAULT = 'is_default';

    const PRODUCT_QUANTITY = 'product_quantity';

    const ATTRIBUTES = [
        self::BRAND_ID,
        self::OPTION_ID,
        self::PAGE_TITLE,
        self::URL_KEY,
        self::IMAGE,
        self::SHORT_DESCRIPTION,
        self::DESCRIPTION,
        self::IS_FEATURED,
        self::STATIC_BLOCK,
        self::META_TITLE,
        self::META_KEYWORDS,
        self::META_DESCRIPTION
    ];

    /**
     * Brand id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set brand id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Eav Option id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Set Eav Option id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setOptionId($id);

    /**
     * @return string|null
     */
    public function getPageTitle();

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setPageTitle($title);

    /**
     * @return string|null
     */
    public function getUrlKey();

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrlKey($url);

    /**
     * @return string|null
     */
    public function getImage();

    /**
     * @param string $image
     *
     * @return $this
     */
    public function setImage($image);

    /**
     * @return string|null
     */
    public function getShortDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShortDescription($value);

    /**
     * @return string|null
     */
    public function getDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDescription($value);

    /**
     * @return int|null
     */
    public function getIsFeatured();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setIsFeatured($value);

    /**
     * @return string|null
     */
    public function getStaticBlock();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStaticBlock($value);

    /**
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMetaTitle($value);

    /**
     * @return string|null
     */
    public function getMetaKeywords();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMetaKeywords($value);

    /**
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMetaDescription($value);

    /**
     * Get option label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set option label
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get option value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set option value
     *
     * @param string $value
     *
     * @return string
     */
    public function setValue($value);

    /**
     * Get option order
     *
     * @return int|null
     */
    public function getSortOrder();

    /**
     * Set option order
     *
     * @param int $sortOrder
     *
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * is default
     *
     * @return bool|null
     */
    public function getIsDefault();

    /**
     * set is default
     *
     * @param bool $isDefault
     *
     * @return $this
     */
    public function setIsDefault($isDefault);

    /**
     * Get option label for store scopes
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionLabelInterface[]|null
     */
    public function getStoreLabels();

    /**
     * Set option label for store scopes
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionLabelInterface[] $storeLabels
     *
     * @return $this
     */
    public function setStoreLabels(array $storeLabels = null);

    /**
     * Get product quantity for brand
     *
     * @return int
     */
    public function getProductQuantity();

    /**
     * Set product quantity for brand
     *
     * @param int $quantity
     *
     * @return $this
     */
    public function setProductQuantity($quantity);
}
