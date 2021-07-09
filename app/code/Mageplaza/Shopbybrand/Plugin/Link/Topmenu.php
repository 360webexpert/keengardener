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

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Shopbybrand\Block\Brand;

/**
 * Class Topmenu
 * @package Mageplaza\Shopbybrand\Plugin\Link
 */
class Topmenu
{
    /**
     * @param \Magento\Theme\Block\Html\Topmenu $topmenu
     * @param $html
     *
     * @return string
     * @throws LocalizedException
     */
    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $topmenu, $html)
    {
        $brandHtml = $topmenu->getLayout()->createBlock(Brand::class)
            ->setTemplate('Mageplaza_Shopbybrand::position/topmenu.phtml')->toHtml();

        return $html . $brandHtml;
    }
}
