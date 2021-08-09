<?php

namespace Ess\M2ePro\Block\Adminhtml\ControlPanel\Inspection;

use Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractGrid as WidgetAbstractGrid;
use Ess\M2ePro\Model\ControlPanel\Inspection\Manager;
use Ess\M2ePro\Model\ControlPanel\Inspection\Result;
use Ess\M2ePro\Model\ResourceModel\Collection\Custom;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;

class Grid extends WidgetAbstractGrid
{
    const NOT_SUCCESS_FILTER = 'not-success';

    /** @var \Ess\M2ePro\Model\ResourceModel\Collection\CustomFactory */
    protected $customCollectionFactory;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper);

        $this->customCollectionFactory = $customCollectionFactory;
        $this->objectManager = $objectManager;
        $this->setId('controlPanelInspectionsGrid');

        $this->setDefaultSort('state');
        $this->setDefaultDir('DESC');
        $this->setDefaultFilter(['state' => self::NOT_SUCCESS_FILTER]);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = $this->customCollectionFactory->create();
        $manager = $this->objectManager->create(\Ess\M2ePro\Model\ControlPanel\Inspection\Manager::class);

        foreach ($manager->getInspections() as $inspection) {
            /** @var \Ess\M2ePro\Model\ControlPanel\Inspection\AbstractInspection $inspection */
            $row = [
                'id' => $manager->getId($inspection),
                'title' => $inspection->getTitle(),
                'description' => $inspection->getDescription(),
                'execution_speed' => $inspection->getExecutionSpeed(),
                'state' => (string)$inspection->getState(),
                'need_attention' => (string)(int)($inspection->getState() > Result::STATE_NOTICE),
                'inspection' => $inspection
            ];
            $collection->addItem(new DataObject($row));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            [
                'header' => $this->__('Title'),
                'align' => 'left',
                'type' => 'text',
                'width' => '20%',
                'index' => 'title',
                'filter_index' => 'title',
                'filter_condition_callback' => [$this, 'callbackFilterLike'],
                'frame_callback' => [$this, 'callbackColumnTitle']
            ]
        );

        $this->addColumn(
            'details',
            [
                'header' => $this->__('Details'),
                'align' => 'left',
                'type' => 'text',
                'width' => '40%',
                'filter_index' => false,
                'frame_callback' => [$this, 'callbackColumnDetails']
            ]
        );

        $this->addColumn(
            'state',
            [
                'header' => $this->__('State'),
                'align' => 'right',
                'width' => '10%',
                'index' => 'state',
                'type' => 'options',
                'options' => [
                    self::NOT_SUCCESS_FILTER => $this->__('Error | Warning'),
                    Result::STATE_ERROR => $this->__('Error'),
                    Result::STATE_WARNING => $this->__('Warning'),
                    Result::STATE_NOTICE => $this->__('Notice'),
                    Result::STATE_SUCCESS => $this->__('Success'),
                ],
                'filter_index' => 'state',
                'filter_condition_callback' => [$this, 'callbackFilterMatch'],
                'frame_callback' => [$this, 'callbackColumnState']
            ]
        );

        $this->addColumn(
            'execution_speed',
            [
                'header'       => $this->__('Execution Speed'),
                'align'        => 'right',
                'type'         => 'options',
                'options'      => [
                    Manager::EXECUTION_SPEED_FAST => $this->__('Fast'),
                    Manager::EXECUTION_SPEED_SLOW => $this->__('Slow')
                ],
                'width'        => '10%',
                'index'        => 'execution_speed',
                'filter_index' => 'execution_speed',
                'filter_condition_callback' => [$this, 'callbackFilterMatch'],
                'frame_callback' => [$this, 'callbackColumnSpeed']
            ]
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('m2epro/controlPanel/InspectionTab', ['_current' => true]);
    }

    //########################################

    protected function callbackFilterLike($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value == null || empty($field)) {
            return;
        }

        $this->getCollection()->addFilter($field, $value, Custom::CONDITION_LIKE);
    }

    protected function callbackFilterMatch($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value == null || empty($field)) {
            return;
        }

        if ($value == self::NOT_SUCCESS_FILTER) {
            $field = 'need_attention';
            $value = '1';
        }

        $this->getCollection()->addFilter($field, $value, Custom::CONDITION_LIKE);
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        /** @var \Ess\M2ePro\Model\ControlPanel\Inspection\AbstractInspection $inspection */
        $inspection = $row->getData('inspection');

        $value = <<<HTML
<span style="color: grey;">[{$inspection->getGroup()}]</span> {$value}
HTML;

        if (!$row->getData('description')) {
            return $value;
        }

        return <<<HTML
<style>
    .admin__field-tooltip .admin__field-tooltip-content {
    bottom: 5rem;
    }
</style>
{$value}
<div class="m2epro-field-tooltip-to-right admin__field-tooltip">
    <a class="admin__field-tooltip-action"  style="bottom:8px;"></a>
    <div class="admin__field-tooltip-content">
           {$row->getData('description')}
    </div>
</div>
HTML;
    }

    public function callbackColumnDetails($value, $row, $column, $isExport)
    {
        /** @var Ess\M2ePro\Model\ControlPanel\Inspection\AbstractInspection $inspection */
        $inspection = $row->getData('inspection');
        $this->js->addOnReadyJs(<<<JS
require([
    'M2ePro/ControlPanel/Inspection'
], function(){

    window.ControlPanelInspectionObj = new ControlPanelInspection();
});
JS
        );

        $html = '';
        foreach ($inspection->getResults() as $result) {
            $html .= '<div>';
            $html .= <<<HTML
{$this->getMarkupByResult($result->getState(), $result->getMessage())}
HTML;
            if ($result->getMetadata()) {
                $html .= <<<HTML
&nbsp;&nbsp;
<a href="javascript://" onclick="ControlPanelInspectionObj.showMetaData(this);">[{$this->__('details')}]</a>
<div class="no-display">{$result->getMetadata()}</div>
HTML;
            }

            $html .= '</div>';
        }

        return $html;
    }

    public function callbackColumnState($value, $row, $column, $isExport)
    {
        return $this->getMarkupByResult($row->getData($column->getIndex()), $value);
    }

    public function callbackColumnSpeed($value, $row, $column, $isExport)
    {
        /** @var \Ess\M2ePro\Model\ControlPanel\Inspection\AbstractInspection $inspection */
        $inspection = $row->getData('inspection');

        return <<<HTML
{$value} <span style="color: grey;">[{$inspection->getTimeToExecute()} sec.]</span>
HTML;
    }

    //########################################

    protected function getMarkupByResult($result, $text)
    {
        switch ($result) {
            case Result::STATE_ERROR:
                return "<span style='color: red; font-weight: bold;'>{$text}</span>";

            case Result::STATE_WARNING:
                return "<span style='color: darkorange; font-weight: bold;'>{$text}</span>";

            case Result::STATE_NOTICE:
                return "<span style='color: dodgerblue; font-weight: bold;'>{$text}</span>";

            case Result::STATE_SUCCESS:
                return "<span style='color: green; font-weight: bold;'>{$text}</span>";
        }

        return $text;
    }
}
