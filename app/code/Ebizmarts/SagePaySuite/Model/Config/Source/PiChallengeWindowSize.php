<?php
/**
 * Copyright © 2019 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PiChallengeWindowSize implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray() : array
    {
        return [
            [
                'value' => 'Small',
                'label' => __('250px x 400px'),
            ],
            [
                'value' => 'Medium',
                'label' => __('390px x 400px'),
            ],
            [
                'value' => 'Large',
                'label' => __('500px x 600px'),
            ],
            [
                'value' => 'ExtraLarge',
                'label' => __('600px x 400px'),
            ],
            [
                'value' => 'FullScreen',
                'label' => __('Fullscreen')
            ]
        ];
    }
}