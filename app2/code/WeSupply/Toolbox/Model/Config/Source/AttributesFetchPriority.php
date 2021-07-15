<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AttributesFetchPriority
 * @package WeSupply\Toolbox\Model\Config\Source
 */
class AttributesFetchPriority implements OptionSourceInterface
{
    /**
     * @var array Option codes
     */
    const FETCH_CODES = [
        'itself_parent',  // associated simple first than parent product
        'parent_itself',  // parent product than associated simple
        'itself_only',   // associated simple only
        'parent_only'    // parent only
    ];

    /**
     * @var array Option Labels
     */
    const FETCH_LABELS = [
        'From Simple Product or fallback to its Parent Product',
        'From Parent Product or fallback to Simple Product',
        'From Simple Product Only',
        'From Parent Product Only',
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach (self::FETCH_CODES as $index => $code) {
            $options[] = [
                'value' => $code,
                'label' => __((self::FETCH_LABELS)[$index])
            ];
        }

        return $options;
    }
}