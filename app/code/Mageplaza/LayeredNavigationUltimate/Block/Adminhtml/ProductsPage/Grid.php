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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Block\Adminhtml\ProductsPage;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Store\Model\System\Store;
use Mageplaza\LayeredNavigationUltimate\Model\ResourceModel\ProductsPage\Collection;

/**
 * Class Grid
 * @package Mageplaza\LayeredNavigationUltimate\Block\Adminhtml\ProductsPage
 */
class Grid extends Extended
{
    /** @var \Mageplaza\LayeredNavigationUltimate\Model\ResourceModel\ProductsPage\Collection */
    protected $_collectionFactory;

    /** @var \Magento\Config\Model\Config\Source\Enabledisable */
    protected $_booleanOptions;

    /** @var \Magento\Store\Model\System\Store */
    protected $_systemStore;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Config\Model\Config\Source\Enabledisable $booleanOptions
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Mageplaza\LayeredNavigationUltimate\Model\ResourceModel\ProductsPage\Collection $collectionFactory
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
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('productPageGrid');
        $this->setDefaultSort('page_id');
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
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn('page_id', [
            'header'           => __('ID'),
            'type'             => 'number',
            'index'            => 'page_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);

        $this->addColumn('name', [
            'header' => __('Name'),
            'index'  => 'name'
        ]);

        $this->addColumn('route', [
            'header' => __('Url key'),
            'index'  => 'route'
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
                    'field'   => 'page_id'
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
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\DataObject $column
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
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
        $this->setMassactionIdField('page_id');
        $this->getMassactionBlock()->setFormFieldName('page_id');

        $this->getMassactionBlock()->addItem('delete', [
            'label'   => __('Delete'),
            'url'     => $this->getUrl('mplayer/*/massDelete'),
            'confirm' => __('Are you sure?')
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mplayer/*/index', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('mplayer/*/edit', ['page_id' => $row->getId()]);
    }
}
