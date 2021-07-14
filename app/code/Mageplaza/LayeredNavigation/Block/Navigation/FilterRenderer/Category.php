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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Block\Navigation\FilterRenderer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Mageplaza\LayeredNavigation\Helper\Data;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Source\ExpandSubcategories;
use Mageplaza\LayeredNavigation\Model\Layer\Filter;

/**
 * Class FilterRenderer
 * @package Mageplaza\LayeredNavigation\Block\Navigation\FilterRenderer
 */
class Category extends Template
{
    const LEVEL = 1;

    protected $_template = 'Mageplaza_LayeredNavigation::layer/category/filter.phtml';
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Category constructor.
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
     * @param null $path
     *
     * @return string
     * @throws LocalizedException
     */
    public function render($path = null)
    {
        $this->setPath($path);
        $filter      = $this->getFilter();
        $filterItems = $filter->getItems();
        $this->assign('filterItems', $filterItems);

        return $this->toHtml();
    }

    /**
     * @param $path
     *
     * @return string
     * @throws LocalizedException
     */
    public function renderChildrenItems($path)
    {
        return $this->getLayout()
            ->createBlock(self::class)
            ->setFilter($this->getFilter())
            ->setLevel($this->getLevel() + self::LEVEL)
            ->render($path);
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->getData('level') ?: self::LEVEL;
    }

    /**
     * @return \Mageplaza\LayeredNavigation\Model\Layer\Filter\Category
     * @throws LocalizedException
     */
    public function getFilter()
    {
        if (!$this->getData('filter') instanceof \Mageplaza\LayeredNavigation\Model\Layer\Filter\Category) {
            throw new LocalizedException(__('Wrong Filter Type'));
        }

        return $this->getData('filter');
    }

    /**
     * @return Filter
     */
    public function getFilterModel()
    {
        return $this->helperData->getFilterModel();
    }

    /**
     * @param $item
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isExpandByClick($item)
    {
        $filter      = $this->getFilter();
        $filterItems = $filter->getItems();

        return $filterItems->hasChildrenData($item->getValue())
            && $filter->getCategoryAttributeModel()->getData('expand_subcategories') === ExpandSubcategories::CLICK;
    }
}
