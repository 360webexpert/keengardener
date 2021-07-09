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

use Magento\Framework\View\Element\Template;
use Mageplaza\LayeredNavigationPro\Helper\Data as LayerHelper;

/**
 * Class AbstractType
 * @package Mageplaza\LayeredNavigationPro\Block\Type
 */
class AbstractType extends Template
{
    /** @var string Path to template file. */
    protected $_template = '';

    /** @var \Magento\Catalog\Model\Layer\Filter\AbstractFilter */
    protected $filter;

    /** @var \Mageplaza\LayeredNavigationPro\Helper\Data */
    protected $helper;

    /**
     * AbstractType constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageplaza\LayeredNavigationPro\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        LayerHelper $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mageplaza\LayeredNavigationPro\Helper\Data
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
     * @return \Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     *
     * @return $this
     */
    public function setFilter(\Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return \Mageplaza\LayeredNavigationPro\Model\Layer\Filter
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
