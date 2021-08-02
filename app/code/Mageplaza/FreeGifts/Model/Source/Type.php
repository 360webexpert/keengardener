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
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Model\Source;

/**
 * Class Type
 * @package Mageplaza\FreeGifts\Model
 */
class Type extends OptionArray
{
    const AUTOMATIC = 'automatic';
    const MANUAL = 'manual';

    /**
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::AUTOMATIC => __('Automatic'),
            self::MANUAL => __('Manual'),
        ];
    }
}
