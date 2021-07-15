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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Model\Config\Source\Widget;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ShowType
 * @package Mageplaza\Shopbybrand\Model\Config\Source\Widget
 */
class ShowType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'option_id', 'label' => __('Brand By OptionID Widget')],
            ['value' => 'featured', 'label' => __('Featured Brand Widget')]
        ];
    }
}
