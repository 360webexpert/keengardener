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
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Search\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Reindex
 * @package Mageplaza\Search\Model\Config\Source
 */
class Reindex implements ArrayInterface
{
    const TYPE_CRON_JOB = 'cronjob';
    const TYPE_PRODUCT_SAVE = 'product_save';
    const TYPE_MANUAL = 'manual';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::TYPE_CRON_JOB => __('Cron job'),
            self::TYPE_PRODUCT_SAVE => __('After Product Save'),
            self::TYPE_MANUAL => __('Manually (used reindex button bellow)')
        ];
    }
}
