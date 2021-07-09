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

namespace Mageplaza\SeoDashboard\Block\Adminhtml\Dashboard\Tab;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\DataObject;
use Mageplaza\SeoDashboard\Block\Adminhtml\Dashboard\Tab;
use Mageplaza\SeoDashboard\Helper\Data;
use Mageplaza\SeoDashboard\Helper\Data as SeoDashboardData;
use Mageplaza\SeoDashboard\Helper\Report;
use Mageplaza\SeoDashboard\Model\ResourceModel\Issue\CollectionFactory;

/**
 * Class Duplicate
 * @package Mageplaza\SeoDashboard\Block\Adminhtml\Dashboard\Tab
 */
class Duplicate extends Tab
{
    /**
     * View more url
     */
    const VIEW_MORE_URL = 'seo/duplicate';

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Duplicate constructor.
     *
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param Report $report
     * @param SeoDashboardData $seoDashboardData
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        CollectionFactory $collectionFactory,
        Report $report,
        SeoDashboardData $seoDashboardData,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;

        parent::__construct($seoDashboardData, $context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mpSeoDbDuplicateContent');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create()
            ->addFieldToFilter('issue_type', Data::ISSUE_TYPE_DUPLICATE)
            ->setPageSize(10)
            ->setCurPage(1)
            ->setOrder('issue_id');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
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

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    /**
     * Get View more url
     *
     * @return string
     */
    public function getViewMoreUrl()
    {
        return $this->_urlBuilder->getUrl(self::VIEW_MORE_URL);
    }

    /**
     * Add multi store column to grid
     *
     * @param string $columnId
     * @param array|DataObject $column
     *
     * @return  $this
     * @throws  Exception
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
