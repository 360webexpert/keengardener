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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class DisplayType
 * @package Mageplaza\LayeredNavigationPro\Model\Config\Source
 */
class DisplayType implements OptionSourceInterface
{
    const LABEL = '0';
    const IMAGE_LABEL = '1';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LABEL, 'label' => __('Label')],
            ['value' => self::IMAGE_LABEL, 'label' => __('Image and Label')]
        ];
    }
}
