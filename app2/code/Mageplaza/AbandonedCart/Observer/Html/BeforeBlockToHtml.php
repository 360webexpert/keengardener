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

namespace Mageplaza\AbandonedCart\Observer\Html;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid;
use Mageplaza\AbandonedCart\Model\Sales\CouponGridFiltererFactory;

/**
 * Class BeforeBlockToHtml
 * @package Mageplaza_AbandonedCart\Observer\Html
 */
class BeforeBlockToHtml implements ObserverInterface
{
    /**
     * @var CouponGridFiltererFactory
     */
    private $couponGridFiltererFactory;

    /**
     * BeforeBlockToHtml constructor.
     *
     * @param CouponGridFiltererFactory $couponGridFiltererFactory
     */
    public function __construct(
        CouponGridFiltererFactory $couponGridFiltererFactory
    ) {
        $this->couponGridFiltererFactory = $couponGridFiltererFactory;
    }

    /**
     * @param Observer $observer
     *
     * @return null
     */
    public function execute(Observer $observer)
    {
        $grid = $observer->getBlock();

        /**
         * \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid
         */
        if ($grid instanceof Grid) {
            $grid->addColumnAfter(
                'generated_by_ace',
                [
                    'header'                    => __('Generated by Abandoned Cart'),
                    'index'                     => 'mp_generated_by_abandoned_cart',
                    'type'                      => 'options',
                    'default'                   => '',
                    'options'                   => ['null' => 'No', '1' => 'Yes'],
                    'width'                     => '30',
                    'align'                     => 'center',
                    'filter_condition_callback' => [
                        $this->couponGridFiltererFactory->create(),
                        'filterByGeneratedByAbandonedCart'
                    ]
                ],
                'created_at'
            )->addColumnAfter(
                'mp_ace_expires_at',
                [
                    'header'  => __('Expires At Abandoned Cart'),
                    'index'   => 'mp_ace_expires_at',
                    'type'    => 'datetime',
                    'default' => '',
                    'width'   => 30,
                    'align'   => 'center',
                ],
                'generated_by_ace'
            );
        }
    }
}
