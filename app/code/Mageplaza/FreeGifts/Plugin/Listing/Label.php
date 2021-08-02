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
 * @package     Mageplaza_ProductLabels
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Plugin\Listing;

use Closure;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\FreeGifts\Helper\Data as HelperData;

/**
 * Class Label
 * @package Mageplaza\FreeGifts\Plugin\Listing
 */
class Label
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var int
     */
    protected $temp = 0;

    /**
     * Label constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->_helperData = $helperData;
    }

    /**
     * @param ListProduct $subject
     * @param Closure $proceed
     * @param $product
     *
     * @return mixed|string
     * @throws LocalizedException
     */
    public function aroundGetProductDetailsHtml(ListProduct $subject, Closure $proceed, $product)
    {
        $isEnabled     = $this->_helperData->isEnabled();
        $isAjaxRequest = $subject->getRequest()->isAjax() && $isEnabled;
        $result        = $proceed($product);

        if ($isEnabled || $isAjaxRequest) {
            $result .= $subject->getLayout()
                ->createBlock(\Mageplaza\FreeGifts\Block\Listing\Label::class)
                ->setTemplate('Mageplaza_FreeGifts::listing/view/label.phtml')
                ->setCartProduct($product)
                ->toHtml();
        }

        return $result;
    }
}
