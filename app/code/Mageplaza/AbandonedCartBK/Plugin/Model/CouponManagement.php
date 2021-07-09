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

namespace Mageplaza\AbandonedCart\Plugin\Model;

use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\AbandonedCart\Helper\Data;

/**
 * Class CouponManagement
 * @package Mageplaza\MultipleCoupons\Plugin\Model
 */
class CouponManagement
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * CouponManagement constructor.
     *
     * @param Data $helperData
     * @param Escaper $escaper
     */
    public function __construct(
        Data $helperData,
        Escaper $escaper
    ) {
        $this->helperData = $helperData;
        $this->escaper    = $escaper;
    }

    /**
     * @param \Magento\Quote\Model\CouponManagement $subject
     * @param $cartId
     * @param $couponCode
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeSet(\Magento\Quote\Model\CouponManagement $subject, $cartId, $couponCode)
    {
        if ($this->helperData->isExpiredCoupon($couponCode)) {
            throw new NoSuchEntityException(__(
                'The coupon code "%1" is not valid.',
                $this->escaper->escapeHtml($couponCode)
            ));
        }

        return [$cartId, $couponCode];
    }
}
