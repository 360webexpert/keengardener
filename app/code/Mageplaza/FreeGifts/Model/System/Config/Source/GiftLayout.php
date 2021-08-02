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
 * @category  Mageplaza
 * @package   Mageplaza_FreeGifts
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Model\System\Config\Source;

use Mageplaza\FreeGifts\Model\Source\OptionArray;

/**
 * Class GiftLayout
 * @package Mageplaza\FreeGifts\Model\System\Config\Source
 */
class GiftLayout extends OptionArray
{
    const LAYOUT_GRID = 'grid';
    const LAYOUT_LIST = 'list';
    const LAYOUT_SLIDER = 'slider';

    /**
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::LAYOUT_LIST => __('List'),
            self::LAYOUT_GRID => __('Grid'),
            self::LAYOUT_SLIDER => __('Slider'),
        ];
    }
}
