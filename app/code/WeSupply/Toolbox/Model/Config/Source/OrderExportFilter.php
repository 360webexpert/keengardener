<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class OrderExportFilter
 * @package WeSupply\Toolbox\Model\Config\Source
 */

class OrderExportFilter implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'allow_all',
                'label' => __('All Orders')
            ],
            [
                'value' => 'exclude_all',
                'label' => __('No Orders')
            ],
            [
                'value' => 'exclude_specific',
                'label' => __('Exclude Specific Orders')
            ]
        ];
    }
}