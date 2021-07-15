<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Block\Adminhtml\Methods\Edit\Tab\Rates\Grid;

/**
 * Columns for Shipping Rate Grid
 */
class ColumnSet extends \Magento\Backend\Block\Widget\Grid\ColumnSet
{
    protected $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory $generatorFactory,
        \Magento\Backend\Model\Widget\Grid\SubTotals $subtotals,
        \Magento\Backend\Model\Widget\Grid\Totals $totals,
        \Amasty\ShippingTableRates\Helper\Data $helper,
        array $data
    ) {
        $this->helper = $helper;
        parent::__construct($context, $generatorFactory, $subtotals, $totals, $data);
    }

    protected function _prepareLayout()
    {
        $this->addColumn('country', [
            'header' => __('Country'),
            'header_export' => 'country',
            'index' => 'country',
            'type' => 'options',
            'options' => $this->helper->getCountries(),
        ]);

        $this->addColumn('state', [
            'header' => __('State'),
            'header_export' => 'state',
            'index' => 'state',
            'type' => 'options',
            'options' => $this->helper->getStates(),
        ]);

        $this->addColumn('city', [
            'header' => __('City'),
            'header_export' => 'city',
            'index' => 'city',
            'type' => 'text',
        ]);

        $this->addColumn('zip_from', [
            'header' => __('Zip From'),
            'header_export' => 'zip_from',
            'index' => 'zip_from',
        ]);

        $this->addColumn('zip_to', [
            'header' => __('Zip To'),
            'header_export' => 'zip_to',
            'index' => 'zip_to',
        ]);

        $this->addColumn('price_from', [
            'header' => __('Price From'),
            'header_export' => 'price_from',
            'index' => 'price_from',
        ]);

        $this->addColumn('price_to', [
            'header' => __('Price To'),
            'header_export' => 'price_to',
            'index' => 'price_to',
        ]);

        $this->addColumn('weight_from', [
            'header' => __('Weight From'),
            'header_export' => 'weight_from',
            'index' => 'weight_from',
        ]);

        $this->addColumn('weight_to', [
            'header' => __('Weight To'),
            'header_export' => 'weight_to',
            'index' => 'weight_to',
        ]);

        $this->addColumn('qty_from', [
            'header' => __('Qty From'),
            'header_export' => 'qty_from',
            'index' => 'qty_from',
        ]);

        $this->addColumn('qty_to', [
            'header' => __('Qty To'),
            'header_export' => 'qty_to',
            'index' => 'qty_to',
        ]);

        $this->addColumn('shipping_type', [
            'header' => __('Shipping Type'),
            'header_export' => 'shipping_type',
            'index' => 'shipping_type',
            'type' => 'options',
            'options' => $this->helper->getTypes(),
        ]);

        $this->addColumn('cost_base', [
            'header' => __('Rate'),
            'header_export' => 'rate',
            'index' => 'cost_base',
        ]);

        $this->addColumn('cost_percent', [
            'header' => __('PPP'),
            'header_export' => 'ppp',
            'index' => 'cost_percent',
        ]);

        $this->addColumn('cost_product', [
            'header' => __('FRPP'),
            'header_export' => 'frpp',
            'index' => 'cost_product',
        ]);

        $this->addColumn('cost_weight', [
            'header' => __('FRPUW'),
            'header_export' => 'frpuw',
            'index' => 'cost_weight',
        ]);

        $this->addColumn('start_weight', [
            'header' => __('Count weight from'),
            'header_export' => 'start_weight',
            'index' => 'start_weight',
        ]);

        $this->addColumn('time_delivery', [
            'header' => __('Estimated Delivery (days)'),
            'header_export' => 'estimated_delivery',
            'index' => 'time_delivery',
        ]);

        $this->addColumn('name_delivery', [
            'header' => __('Name delivery'),
            'header_export' => 'name_delivery',
            'index' => 'name_delivery',
        ]);

        $link = $this->getUrl('amstrates/rates/delete') . 'id/$id';
        $this->addColumn('action', [
            'header' => __('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getVid',
            'actions' => [
                [
                    'caption' => __('Delete'),
                    'url' => $link,
                    'field' => 'id',
                    'confirm' => __('Are you sure?')
                ]
            ],
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ]);
        return parent::_prepareLayout();
    }

    public function addColumn($title, $data)
    {
        $column = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Grid\Column::class, $title)
            ->addData($data);
        $this->setChild($title, $column);
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('amstrates/rates/edit', ['id' => $item->getId()]);
    }
}
