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

namespace Mageplaza\Shopbybrand\Plugin\Link;

use Mageplaza\Shopbybrand\Block\Brand;

/**
 * Class TopmenuPorto
 * @package Mageplaza\Shopbybrand\Plugin\Link
 */
class TopmenuPorto
{
    /**
     * @param $topmenu
     * @param $html
     *
     * @return string
     */
    public function afterGetMegamenuHtml($topmenu, $html)
    {
        $brandHtml = $topmenu->getLayout()->createBlock(Brand::class)
            ->setTemplate('Mageplaza_Shopbybrand::position/topmenuporto.phtml')->toHtml();

        return $html . $brandHtml;
    }
}
