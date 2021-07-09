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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Plugin;

use Closure;

/**
 * Class ConversionFunnel
 * @package Mageplaza\AbandonedCart\Plugin
 */
class ConversionFunnel
{
    /**
     * @param \Mageplaza\Reports\Block\Dashboard\ConversionFunnel $subject
     * @param Closure $process
     *
     * @return string
     */
    public function aroundGetDetailUrl(\Mageplaza\Reports\Block\Dashboard\ConversionFunnel $subject, Closure $process)
    {
        return $subject->getUrl('abandonedcart/index/checkoutreport');
    }

    /**
     * @param \Mageplaza\Reports\Block\Dashboard\ConversionFunnel $subject
     * @param Closure $process
     *
     * @return bool
     */
    public function aroundCanShowDetail(\Mageplaza\Reports\Block\Dashboard\ConversionFunnel $subject, Closure $process)
    {
        return true;
    }
}
