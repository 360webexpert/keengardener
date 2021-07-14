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
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Block\Adminhtml\Low;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\SeoDashboard\Helper\Data as SeoDashboardData;
use Mageplaza\SeoDashboard\Model\ResourceModel\Low\CollectionFactory;

/**
 * Class Grid
 * @package Mageplaza\SeoDashboard\Block\Adminhtml\Low
 */
class Grid extends Extended
{
    /**
     * @type CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var SeoDashboardData
     */
    protected $_seoDashboardData;

    /**
     * Constructor
     *
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param SeoDashboardData $seoDashboardData
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        CollectionFactory $collectionFactory,
        SeoDashboardData $seoDashboardData,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_seoDashboardData  = $seoDashboardData;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('lowWordCountGrid');
        $this->setDefaultSort('issue_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('issue_id', [
            'header'           => __('ID'),
            'type'             => 'number',
            'index'            => 'issue_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);

        $this->addColumn('entity_ids', [
            'header'           => __('Case'),
            'sortable'         => false,
            'index'            => 'entity_ids',
            'renderer'         => '\Mageplaza\SeoDashboard\Block\Adminhtml\Renderer\Duplicate',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name'
        ]);

        $this->addColumn('fields', [
            'header'   => __('Field'),
            'sortable' => false,
            'index'    => 'fields',
            'type'     => 'options',
            'options'  => $this->_seoDashboardData->getFieldOptions()
        ]);

        $this->addColumn('Count', [
            'header'   => __('Count'),
            'sortable' => false,
            'index'    => 'count',
            'renderer' => '\Mageplaza\SeoDashboard\Block\Adminhtml\Renderer\Count',
        ]);

        $this->addColumn('entity', [
            'header'   => __('Entity'),
            'sortable' => false,
            'index'    => 'entity',
            'renderer' => '\Mageplaza\SeoDashboard\Block\Adminhtml\Renderer\UcFirst',
        ]);

        $this->addColumn('store', [
            'header'     => __('Store View'),
            'sortable'   => false,
            'index'      => 'store',
            'type'       => 'store',
            'store_view' => true
        ]);

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Add Multi store column
     *
     * @param $columnId
     * @param $column
     *
     * @return $this
     * @throws Exception
     * @throws LocalizedException
     */
    public function addMultiStoreColumn($columnId, $column)
    {
        if (is_array($column)) {
            $this->getColumnSet()->setChild(
                $columnId,
                $this->getLayout()
                    ->createBlock('Magento\Backend\Block\Widget\Grid\Column\Multistore')
                    ->setData($column)
                    ->setId($columnId)
                    ->setGrid($this)
            );
            $this->getColumnSet()->getChildBlock($columnId)->setGrid($this);
        } else {
            throw new Exception(__('Please correct the column format and try again.'));
        }

        $this->_lastColumnId = $columnId;

        return $this;
    }
}
