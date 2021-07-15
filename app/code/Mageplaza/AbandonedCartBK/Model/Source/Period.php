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
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Period
 * @package Mageplaza\RewardPointsUltimate\Model\Source
 */
class Period implements OptionSourceInterface
{
    const DAY   = 'day';
    const WEEK  = 'week';
    const MONTH = 'month';
    const YEAR  = 'year';

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    protected function getOptionArray()
    {
        return [
            self::DAY   => __('Day'),
            self::WEEK  => __('Week'),
            self::MONTH => __('Month'),
            self::YEAR  => __('Year')
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
