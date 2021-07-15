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

use Mageplaza\Shopbybrand\Block\Link\CategoryMenu;
use Mageplaza\Shopbybrand\Helper\Data;
use Mageplaza\Shopbybrand\Model\Config\Source\BrandPosition;

/**
 * Class TopMenuPorto
 * @package Mageplaza\Shopbybrand\Plugin\Link
 */
class TopMenuPorto
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * TopMenuPorto constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param $topmenu
     * @param $html
     *
     * @return string
     */
    public function afterGetMegamenuHtml($topmenu, $html)
    {
        if (!$this->helperData->isEnabled() || !$this->helperData->canShowBrandLink(BrandPosition::CATEGORY)) {
            return $html;
        }
        $brandHtml = $topmenu->getLayout()->createBlock(CategoryMenu::class)
            ->setTemplate('Mageplaza_Shopbybrand::position/topmenuporto.phtml')->toHtml();

        return $html . $brandHtml;
    }
}
