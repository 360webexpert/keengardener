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
 * @package     Magepaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Api\Data;

/**
 * Interface BrandCategoryInterface
 * @package Mageplaza\Shopbybrand\Api\Data
 */
interface BrandCategoryInterface
{
    const STATUS           = 'status';
    const STORE_IDS        = 'store_ids';
    const NAME             = 'name';
    const URL_KEY          = 'url_key';
    const META_TITLE       = 'meta_title';
    const META_KEYWORDS    = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const META_ROBOTS      = 'meta_robots';
    const CREATED_AT       = 'created_at';
    const UPDATED_AT       = 'updated_at';

    const ATTRIBUTES = [
        'cat_id',
        self::STATUS,
        self::STORE_IDS,
        self::NAME,
        self::URL_KEY,
        self::META_TITLE,
        self::META_KEYWORDS,
        self::META_DESCRIPTION,
        self::META_ROBOTS,
        self::CREATED_AT,
        self::UPDATED_AT
    ];

    /**
     * Brand category id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set brand category id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getStoreIds();

    /**
     * @param string $store
     *
     * @return $this
     */
    public function setStoreIds($store);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status);

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
     * @return int|null
     */
    public function getMetaRobots();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMetaRobots($value);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Product updated date
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set product updated date
     *
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
