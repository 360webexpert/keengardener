<?php
namespace WeSupply\Toolbox\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class EstimationFormat implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'M d', 'label' => __('Jan 28')],
            ['value' => 'd M', 'label' => __('28 Jan')],
            ['value' => 'F d', 'label' => __('January 28')],
            ['value' => 'd F', 'label' => __('28 January')],
            ['value' => 'D, M d', 'label' => __('Mon, Jan 28')],
            ['value' => 'D, F d', 'label' => __('Mon, January 28')],
            ['value' => 'l, M d', 'label' => __('Monday, Jan 28')],
            ['value' => 'l, F d', 'label' => __('Monday, January 28')],
            ['value' => 'D, M d, Y', 'label' => __('Mon, Jan 28, 2021')],
            ['value' => 'l, M d, Y', 'label' => __('Monday, Jan 28, 2021')],
            ['value' => 'd/m', 'label' => __('28/01')],
            ['value' => 'm/d', 'label' => __('01/28')],
            ['value' => 'd/m/y', 'label' => __('28/01/21')],
            ['value' => 'm/d/y', 'label' => __('01/28/21')],
            ['value' => 'd/m/Y', 'label' => __('28/01/2021')],
            ['value' => 'm/d/Y', 'label' => __('01/28/2021')]
        ];
    }
}
