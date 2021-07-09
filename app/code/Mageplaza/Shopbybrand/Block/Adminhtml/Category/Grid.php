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

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Category;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\DataObject;
use Magento\Store\Model\System\Store;
use Mageplaza\Shopbybrand\Model\ResourceModel\Category\Collection;

/**
 * Class Grid
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Category
 */
class Grid extends Extended
{
    /**
     * @var Collection
     */
    protected $_collectionFactory;

    /**
     * @var Enabledisable
     */
    protected $_booleanOptions;

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * Grid constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param Enabledisable $booleanOptions
     * @param Store $systemStore
     * @param Collection $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Enabledisable $booleanOptions,
        Store $systemStore,
        Collection $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_booleanOptions = $booleanOptions;
        $this->_systemStore = $systemStore;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('categoryGrid');
        $this->setDefaultSort('cat_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->load();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn('cat_id', [
            'header'           => __('ID'),
            'type'             => 'number',
            'index'            => 'cat_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);

        $this->addColumn('name', [
            'header' => __('Name'),
            'index'  => 'name'
        ]);

        $this->addColumn('route', [
            'header' => __('Url key'),
            'index'  => 'url_key'
        ]);

        $this->addColumn('status', [
            'header'  => __('Status'),
            'index'   => 'status',
            'type'    => 'options',
            'options' => $this->getStatusOptions(),
        ]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn('store_ids', [
                'header'                    => __('Store View'),
                'index'                     => 'store_ids',
                'type'                      => 'store',
                'store_all'                 => true,
                'store_view'                => true,
                'sortable'                  => false,
                'filter_condition_callback' => [$this, '_filterStoreCondition']
            ]);
        }

        $this->addColumn('edit', [
            'header'           => __('Edit'),
            'type'             => 'action',
            'getter'           => 'getId',
            'actions'          => [
                [
                    'caption' => __('Edit'),
                    'url'     => [
                        'base' => '*/*/edit'
                    ],
                    'field'   => 'cat_id'
                ]
            ],
            'filter'           => false,
            'sortable'         => false,
            'index'            => 'stores',
            'header_css_class' => 'col-action',
            'column_css_class' => 'col-action'
        ]);

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @param $collection
     * @param DataObject $column
     */
    protected function _filterStoreCondition($collection, DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * Get option hash
     *
     * @return array
     */
    protected function getStatusOptions()
    {
        $options = [];
        foreach ($this->_booleanOptions->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('cat_id');
        $this->getMassactionBlock()->setFormFieldName('cat_id');

        $this->getMassactionBlock()->addItem('delete', [
            'label'   => __('Delete'),
            'url'     => $this->getUrl('mpbrand/*/massDelete'),
            'confirm' => __('Are you sure?')
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpbrand/category/index', ['_current' => true]);
    }

    /**
     * @param Product|Object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['cat_id' => $row->getId()]);
    }
}
