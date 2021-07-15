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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Block\Type;

use Mageplaza\LayeredNavigationPro\Block\Type\Slider as SliderPro;

/**
 * Class Slider
 * @package Mageplaza\LayeredNavigationUltimate\Block\Type
 */
class Slider extends SliderPro
{
    /**
     * Internal constructor, that is called from real constructor
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        /** @var \Mageplaza\LayeredNavigationUltimate\Helper\Data $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get('\Mageplaza\LayeredNavigationUltimate\Helper\Data');
        if ($helper->enableIonRangeSlider()) {
            $this->setTemplate('Mageplaza_LayeredNavigationUltimate::type/slider.phtml');
        }
    }
}
