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

namespace Mageplaza\Shopbybrand\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ShowBrandInfo
 * @package Mageplaza\Shopbybrand\Model\Config\Source
 */
class ShowBrandInfo implements OptionSourceInterface
{
    const BRAND_NAME = 'name';
    const BRAND_LOGO = 'logo';
    const BRAND_DESC = 'desc';
    const NOT_SHOW   = 'not-show';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NOT_SHOW, 'label' => __('Not show')],
            ['value' => self::BRAND_NAME, 'label' => __('Brand Name')],
            ['value' => self::BRAND_LOGO, 'label' => __('Brand Logo')],
            ['value' => self::BRAND_DESC, 'label' => __('Brand Description')]
        ];
    }
}
