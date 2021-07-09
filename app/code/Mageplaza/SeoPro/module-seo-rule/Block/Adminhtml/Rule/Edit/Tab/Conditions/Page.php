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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Block\Adminhtml\Rule\Edit\Tab\Conditions;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;

/**
 * Class Page
 * @package Mageplaza\SeoRule\Block\Adminhtml\Rule\Edit\Tab\Conditions
 */
class Page extends Extended
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Contact factory
     *
     * @var ContactFactory
     */
    protected $contactFactory;

    /**
     * @var  Registry
     */
    protected $registry;

    /**
     * @var ObjectManagerInterface|null
     */
    protected $_objectManager = null;

    /**
     * @var PageCollectionFactory
     */
    protected $pageCollection;

    /**
     * Page constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $registry
     * @param ObjectManagerInterface $objectManager
     * @param CollectionFactory $productCollectionFactory
     * @param PageCollectionFactory $pageCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $registry,
        ObjectManagerInterface $objectManager,
        CollectionFactory $productCollectionFactory,
        PageCollectionFactory $pageCollectionFactory,
        array $data = []
    ) {
        $this->pageCollection           = $pageCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_objectManager           = $objectManager;
        $this->registry                 = $registry;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('PagesGrid');
        $this->setDefaultSort('page_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setDefaultFilter(['in_pages' => 1]);
    }

    /**
     * @param Column $column
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_pages') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('page_id', ['in' => $productIds]);
            } elseif ($productIds) {
                $this->getCollection()->addFieldToFilter('page_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->pageCollection->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_pages', [
            'header_css_class' => 'a-center',
            'type'             => 'checkbox',
            'name'             => 'in_pages',
            'align'            => 'center',
            'index'            => 'page_id',
            'values'           => $this->_getSelectedProducts(),
        ]);
        $this->addColumn('page_id', [
            'header'           => __('Page ID'),
            'type'             => 'number',
            'index'            => 'page_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);
        $this->addColumn('title', [
            'header' => __('Title'),
            'index'  => 'title',
            'class'  => 'xxx',
            'width'  => '50px',
        ]);
        $this->addColumn('position', [
            'header'         => __('Position'),
            'name'           => 'position',
            'width'          => 60,
            'type'           => 'number',
            'validate_class' => 'validate-number',
            'index'          => 'position',
            'editable'       => true,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Get Grid url
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/PagesGrid', ['_current' => true]);
    }

    /**
     * Get row url
     *
     * @param object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return '#';
    }

    /**
     * Get selected products
     * @return array
     */
    protected function _getSelectedProducts()
    {
        if (!empty($this->getRule())) {
            return array_keys($this->getRule());
        }

        return [];
    }

    /**
     * Retrieve selected products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $proIds = [];
        if (!empty($this->getRule())) {
            $proIds = $this->getRule();
        }

        return $proIds;
    }

    /**
     * Get rule from session
     * @return null
     */
    public function getRule()
    {
        if ($rule = $this->_backendSession->getSeoRulePages()) {
            return $rule;
        }

        return null;
    }
}
