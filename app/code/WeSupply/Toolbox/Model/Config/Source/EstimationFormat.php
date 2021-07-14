<?php
namespace WeSupply\Toolbox\Model\Config\Source;

class EstimationFormat implements \Magento\Framework\Option\ArrayInterface
{


    public function toOptionArray()
    {
        return [
            ['value' => 'm/d', 'label' => __('mm/dd (01/28)')],
            ['value' => 'd/m', 'label' => __('dd/mm (28/01)')],
            ['value' => 'F d', 'label' => __('Month Day (January 28)')],
            ['value' => 'd F', 'label' => __('Day Month (28 January)')],
            ['value' => 'M d', 'label' => __('Short Month Day (Jan 28)')],
            ['value' => 'd M', 'label' => __('Day Short Month (28 Jan)')],
            ['value' => 'd/m/Y', 'label' => __('dd/mm/yyyy (28/01/2019)')],
            ['value' => 'm/d/Y', 'label' => __('mm/dd/yyyy (01/28/2019)')],
        ];
    }
}