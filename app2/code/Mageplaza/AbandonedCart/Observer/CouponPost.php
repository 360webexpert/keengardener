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

namespace Mageplaza\AbandonedCart\Observer;

use Magento\Checkout\Controller\Cart\CouponPost as CheckoutCouponPost;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Mageplaza\AbandonedCart\Helper\Data;

/**
 * Class CouponPost
 * @package Mageplaza\AbandonedCart\Observer
 */
class CouponPost implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * CouponPost constructor.
     *
     * @param Data $helperData
     * @param UrlInterface $url
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     */
    public function __construct(
        Data $helperData,
        UrlInterface $url,
        ManagerInterface $messageManager,
        Escaper $escaper
    ) {
        $this->helperData     = $helperData;
        $this->url            = $url;
        $this->messageManager = $messageManager;
        $this->escaper        = $escaper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $couponCode = $observer->getEvent()->getRequest()->getParam('coupon_code');
        if ($this->helperData->isExpiredCoupon($couponCode)) {
            /** @var CheckoutCouponPost $controllerAction */
            $controllerAction = $observer->getControllerAction();
            $controllerAction->getActionFlag()->set('', CheckoutCouponPost::FLAG_NO_DISPATCH, true);
            $this->messageManager->addErrorMessage(
                __(
                    'The coupon code "%1" is not valid.',
                    $this->escaper->escapeHtml($couponCode)
                )
            );

            $controllerAction->getResponse()->setRedirect($this->url->getUrl('checkout/cart'));
        }
    }
}
