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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Image as HelperImage;
use Magento\Catalog\Helper\Output as HelperOutput;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigationPro\Helper\Data;
use Mageplaza\LayeredNavigationPro\Model\Config\Source\DisplayType;

/**
 * Class SubCategory
 * @package Mageplaza\LayeredNavigationPro\Block
 */
class SubCategory extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var HelperOutput
     */
    protected $helperOutput;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var HelperImage
     */
    protected $helperImage;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * SubCategory constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Data $helperData
     * @param HelperOutput $helperOutput
     * @param HelperImage $helperImage
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helperData,
        HelperOutput $helperOutput,
        HelperImage $helperImage,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->helperData = $helperData;
        $this->helperOutput = $helperOutput;
        $this->helperImage = $helperImage;
        $this->categoryRepository = $categoryRepository;
        $this->_storeManager = $storeManager;

        parent::__construct($context, $data);
    }

    /**
     * @return array|Category[]|Collection
     */
    public function getCurrentChildCategories()
    {
        if (!$this->helperData->isSubCategorySliderEnabled()) {
            return [];
        }

        $currentCategory = $this->getCurrentCategory();
        $categoryId = $currentCategory->getId();
        $categoryPages = $this->helperData->getCategory();
        if (!in_array($categoryId, $categoryPages, true)) {
            return [];
        }

        return $currentCategory->getChildrenCategories();
    }

    /**
     * Check activity of category
     *
     * @param Category $category
     *
     * @return  bool
     */
    public function isCategoryActive($category)
    {
        if ($this->getCurrentCategory()) {
            return in_array($category->getId(), $this->getCurrentCategory()->getPathIds(), true);
        }

        return false;
    }

    /**
     * @param Category $category
     *
     * @return bool|string
     */
    public function getCategoryImage($category)
    {
        try {
            $imgUrl = $this->categoryRepository->get($category->getId())->getImageUrl();
            if (!$imgUrl) {
                $imgUrl = $this->helperImage->getDefaultPlaceholderUrl('image');
            }

            $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
            $categoryName = $this->escapeHtml($category->getName());
            $imgUrl = strpos($this->escapeUrl($imgUrl), $baseUrl) !== false ? $this->escapeUrl($imgUrl) : $baseUrl . $this->escapeUrl($imgUrl);
            $imgHtml = '<div class="mplayer-category-item-image">
                            <img src="' . $imgUrl . '"
                                alt="' . $categoryName . '"
                                title="' . $categoryName . '"
                                class="mplayer-category-image" />
                            </div>';
            $imgHtml = $this->helperOutput->categoryAttribute($category, $imgHtml, 'image');
        } catch (LocalizedException $e) {
            $this->_logger->critical($e->getMessage());
            $imgHtml = false;
        }

        return $imgHtml;
    }

    /**
     * @return Category
     */
    protected function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * @return bool
     */
    public function isShowImage()
    {
        return $this->helperData->getSubCategoryConfig('display_type') === DisplayType::IMAGE_LABEL;
    }

    /**
     * @return mixed
     */
    public function getSubCategoryConfig()
    {
        return $this->helperData->getSubCategoryConfig('');
    }
}
