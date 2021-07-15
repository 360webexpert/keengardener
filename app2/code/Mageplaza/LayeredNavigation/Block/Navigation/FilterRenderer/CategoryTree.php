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
 * @package     Mageplaza_LayeredNavigation'
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Block\Navigation\FilterRenderer;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\LayeredNavigation\Block\Navigation\FilterRenderer;
use Mageplaza\LayeredNavigation\Helper\Data;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Source\ExpandSubcategories;

/**
 * Class CategoryTree
 * @package Mageplaza\LayeredNavigation\Block\Navigation\FilterRenderer
 */
class CategoryTree extends FilterRenderer
{
    /**
     * @var CategoryManagementInterface
     */
    protected $categoryManagement;

    /**
     * @var  FilterInterface
     */
    protected $filter;
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * CategoryTree constructor.
     *
     * @param Template\Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @param FilterInterface $filter
     *
     * @return string
     * @throws LocalizedException
     */
    public function render(FilterInterface $filter)
    {
        $categoryTreeHtml = $this->getCategoryTreeHtml($filter);
        $this->assign('categoryTreeHtml', $categoryTreeHtml);

        return $this->_toHtml();
    }

    /**
     * @param $filter
     *
     * @return string
     * @throws LocalizedException
     */
    public function getCategoryTreeHtml($filter)
    {
        return $this->getLayout()
            ->createBlock(Category::class)
            ->setFilter($filter)
            ->render();
    }

    /**
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param $filter
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoExpand()
    {
        $expand = $this->getFilter()->getCategoryAttributeModel()->getData('expand_subcategories');

        return $expand === ExpandSubcategories::AUTO;
    }
}
