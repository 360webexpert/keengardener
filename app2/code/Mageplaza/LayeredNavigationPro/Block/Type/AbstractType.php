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

namespace Mageplaza\LayeredNavigationPro\Block\Type;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\LayeredNavigationPro\Helper\Data as LayerHelper;
use Mageplaza\LayeredNavigationPro\Model\Layer\Filter;

/**
 * Class AbstractType
 * @package Mageplaza\LayeredNavigationPro\Block\Type
 */
class AbstractType extends Template
{
    /** @var string Path to template file. */
    protected $_template = '';

    /** @var AbstractFilter */
    protected $filter;

    /** @var LayerHelper */
    protected $helper;

    /**
     * AbstractType constructor.
     *
     * @param Context $context
     * @param LayerHelper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        LayerHelper $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @return LayerHelper
     */
    public function helper()
    {
        return $this->helper;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->filter->getItems();
    }

    /**
     * @return mixed
     */
    public function isMultipleMode()
    {
        $filter = $this->getFilter();

        return $this->getFilterModel()->isMultiple($filter);
    }

    /**
     * @return AbstractFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param AbstractFilter $filter
     *
     * @return $this
     */
    public function setFilter(AbstractFilter $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return Filter
     */
    public function getFilterModel()
    {
        return $this->helper->getFilterModel();
    }

    /**
     * @return mixed
     */
    public function isSearchEnable()
    {
        $filter = $this->getFilter();

        return $this->getFilterModel()->isSearchEnable($filter);
    }

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->filter->getRequestVar();
    }

    /**
     * @return string
     */
    public function getBlankUrl()
    {
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = [$this->filter->getRequestVar() => $this->filter->getResetValue()];
        $params['_escape'] = true;

        return $this->_urlBuilder->getUrl('*/*/*', $params);
    }
}
