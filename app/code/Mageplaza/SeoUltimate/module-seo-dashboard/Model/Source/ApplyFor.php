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

namespace Mageplaza\SeoDashboard\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Mageplaza\SeoDashboard\Helper\Data;

/**
 * Class ApplyFor
 * @package Mageplaza\SeoDashboard\Model\Source
 */
class ApplyFor implements ArrayInterface
{
    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '',
                'label' => '-- Please select --'
            ],
            [
                'value' => Data::PRODUCT_ENTITY,
                'label' => __('Products')
            ],
            [
                'value' => Data::CATEGORY_ENTITY,
                'label' => __('Categories')
            ],
            [
                'value' => Data::PAGE_ENTITY,
                'label' => __('Pages')
            ],
        ];

        return $options;
    }
}
