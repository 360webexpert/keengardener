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

/**
 * Class Rating
 * @package Mageplaza\LayeredNavigationUltimate\Block\Type
 */
class Rating extends \Mageplaza\LayeredNavigationPro\Block\Type\Rating
{
    /**
     * construct to change template
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->helper->getFilterConfig('rating/show_as_slider')) {
            $this->setTemplate('Mageplaza_LayeredNavigationUltimate::type/ratingSlider.phtml');
        }
    }

    /**
     * add 5 star if no 5 star in rating items
     *
     * @param $items
     *
     * @return array
     */
    public function getRatingItems($items)
    {
        $result = [];
        if (is_array($items) && count($items)) {
            foreach ($items as $item) {
                $result[] = $item->getValue();
            }

            if (max($result) * 20 < 100) {
                array_unshift($result, 5);
            }
        }

        return $result;
    }
}
