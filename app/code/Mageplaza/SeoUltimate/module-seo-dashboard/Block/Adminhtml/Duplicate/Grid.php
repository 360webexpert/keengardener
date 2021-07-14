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

namespace Mageplaza\SeoDashboard\Block\Adminhtml\Duplicate;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Registry;
use Mageplaza\SeoDashboard\Helper\Data;
use Mageplaza\SeoDashboard\Helper\Data as SeoDashboardData;
use Mageplaza\SeoDashboard\Model\ResourceModel\Issue\CollectionFactory;
use Mageplaza\SeoDashboard\Model\ResourceModel\Mediate\CollectionFactory as MediateCollectionFactory;

/**
 * Class Grid
 * @package Mageplaza\SeoDashboard\Block\Adminhtml\Duplicate
 */
class Grid extends Extended
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var MediateCollectionFactory
     */
    protected $_mediateCollectionFactory;

    /**
     * @var SeoDashboardData
     */
    protected $_seoDashboardData;

    /**
     * @type string|int|null
     */
    protected $_issueId = null;

    /**
     * Constructor
     *
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param MediateCollectionFactory $mediateCollectionFactory
     * @param SeoDashboardData $seoDashboardData
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        CollectionFactory $collectionFactory,
        MediateCollectionFactory $mediateCollectionFactory,
        SeoDashboardData $seoDashboardData,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_collectionFactory        = $collectionFactory;
        $this->_mediateCollectionFactory = $mediateCollectionFactory;
        $this->_seoDashboardData         = $seoDashboardData;
        $this->_issueId                  = $coreRegistry->registry('issue_id');

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('duplicateContentGrid');
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
        $collection = $this->_collectionFactory->create()
            ->addFieldToFilter('issue_type', Data::ISSUE_TYPE_DUPLICATE);
        if ($this->_issueId) {
            $issue      = $collection->addFieldToFilter('issue_id', $this->_issueId)->getFirstItem();
            $collection = $this->_mediateCollectionFactory->create()
                ->addFieldToFilter('entity', $issue->getEntity())
                ->addFieldToFilter('store_id', $issue->getStore())
                ->addFieldToFilter('entity_id', ['in' => explode(',', $issue->getEntityIds())]);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare Columns
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        if (!$this->_issueId) {
            $this->addColumn('issue_id', [
                'header'           => __('ID'),
                'type'             => 'number',
                'index'            => 'issue_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]);

            $this->addColumn('entity_ids', [
                'header'           => __('Duplicate case'),
                'sortable'         => false,
                'index'            => 'entity_ids',
                'renderer'         => '\Mageplaza\SeoDashboard\Block\Adminhtml\Renderer\Duplicate',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]);

            $this->addColumn('entity', [
                'header'   => __('Entity'),
                'sortable' => false,
                'index'    => 'entity',
                'renderer' => '\Mageplaza\SeoDashboard\Block\Adminhtml\Renderer\UcFirst',
            ]);

            $this->addColumn('fields', [
                'header'   => __('Field'),
                'sortable' => false,
                'index'    => 'fields',
                'type'     => 'options',
                'options'  => $this->_seoDashboardData->getFieldOptions()
            ]);

            $this->addColumn('store', [
                'header'     => __('Store View'),
                'sortable'   => false,
                'index'      => 'store',
                'type'       => 'store',
                'store_view' => true
            ]);

            $this->addColumn('view', [
                'header'           => __('View'),
                'type'             => 'action',
                'getter'           => 'getId',
                'actions'          => [
                    [
                        'caption' => __('View'),
                        'url'     => [
                            'base' => '*/*/view',
                        ],
                        'field'   => 'issue_id',
                    ],
                ],
                'filter'           => false,
                'sortable'         => false,
                'index'            => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]);
        } else {
            $this->addColumn('entity_id', [
                'header'           => __('Entity Id'),
                'sortable'         => false,
                'index'            => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]);

            $this->addColumn('case', [
                'header'           => __('Duplicate case'),
                'sortable'         => false,
                'index'            => 'entity_id',
                'renderer'         => '\Mageplaza\SeoDashboard\Block\Adminhtml\Renderer\Duplicate\View',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]);

            $this->addColumn('entity', [
                'header'   => __('Entity'),
                'sortable' => false,
                'index'    => 'entity',
                'renderer' => '\Mageplaza\SeoDashboard\Block\Adminhtml\Renderer\UcFirst',
            ]);

            $this->addColumn('store_id', [
                'header'     => __('Store View'),
                'sortable'   => false,
                'index'      => 'store_id',
                'type'       => 'store',
                'store_view' => true
            ]);
        }

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
