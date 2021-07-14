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

namespace Mageplaza\Shopbybrand\Api;

/**
 * Class BrandRepositoryInterface
 * @package Mageplaza\Shopbybrand\Api
 */
interface BrandRepositoryInterface
{
    /**
     * Get brand list
     * @param int|null $storeId
     *
     * @return \Mageplaza\Shopbybrand\Api\Data\BrandInterface[]
     */
    public function getBrandList($storeId = null);

    /**
     * Get brand feature list
     *
     * @return \Mageplaza\Shopbybrand\Api\Data\BrandInterface[]
     */
    public function getFeatureBrand();

    /**
     * @param string $name
     *
     * @return \Mageplaza\Shopbybrand\Api\Data\BrandInterface[]
     */
    public function getBrandByName($name);

    /**
     * @param string $optionId
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProductList($optionId);

    /**
     * @param string $sku
     * @param int|null $storeId
     *
     * @return \Mageplaza\Shopbybrand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrandBySku($sku, $storeId = null);

    /**
     * @param string $optionId
     * @param string $sku
     * @param int|null $storeId
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function setProduct($optionId, $sku, $storeId = null);

    /**
     * @param string $sku
     *
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function deleteProduct($sku);

    /**
     * Add option to brand
     *
     * @param \Mageplaza\Shopbybrand\Api\Data\BrandInterface $option
     *
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function add($option);

    /**
     * @param string $optionId
     * @param \Mageplaza\Shopbybrand\Api\Data\BrandInterface $option
     *
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function update($optionId, $option);

    /**
     * Delete option from brand
     *
     * @param string $optionId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete($optionId);

    /**
     * @return \Mageplaza\Shopbybrand\Api\Data\BrandCategoryInterface[]
     */
    public function getCategory();

    /**
     * @param string $categoryId
     *
     * @return \Mageplaza\Shopbybrand\Api\Data\BrandCategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryById($categoryId);

    /**
     * @param \Mageplaza\Shopbybrand\Api\Data\BrandCategoryInterface $category
     *
     * @return \Mageplaza\Shopbybrand\Api\Data\BrandCategoryInterface
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addCategory($category);

    /**
     * @param string $categoryId
     * @param \Mageplaza\Shopbybrand\Api\Data\BrandCategoryInterface $category
     *
     * @return \Mageplaza\Shopbybrand\Api\Data\BrandCategoryInterface
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateCategory($categoryId, $category);

    /**
     * @param string $categoryId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteCategory($categoryId);

    /**
     * @param string|null $storeId
     *
     * @return \Mageplaza\Shopbybrand\Api\Data\BrandConfigInterface
     */
    public function getBrandConfigs($storeId = null);
}
