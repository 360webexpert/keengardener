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

namespace Mageplaza\SeoDashboard\Block\Adminhtml\NoRoute;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Mageplaza\SeoDashboard\Helper\Data as SeoDashboardData;
use Mageplaza\SeoDashboard\Model\ResourceModel\NoRoute\CollectionFactory;

/**
 * Class Grid
 * @package Mageplaza\SeoDashboard\Block\Adminhtml\NoRoute
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
        $this->setId('noRouteGrid');
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
            ->setOrder('issue_id', 'DESC');

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

        $this->addColumn('uri', [
            'header'           => __('Uri'),
            'sortable'         => false,
            'index'            => 'uri',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name'
        ]);

        $this->addColumn('action', [
            'header'           => __('Action'),
            'type'             => 'action',
            'getter'           => 'getId',
            'actions'          => [
                [
                    'caption' => __('Resolve'),
                    'url'     => [
                        'base' => '*/*/resolve',
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

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
