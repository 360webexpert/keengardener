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

namespace Mageplaza\AbandonedCart\Plugin\App;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Mageplaza\AbandonedCart\Helper\Data;

/**
 * Class FrontControllerPlugin
 * @package Mageplaza\AbandonedCart\Plugin\App
 */
class FrontControllerPlugin
{
    const ABANDONED_CART_PATH = '/abandonedcart/checkout/cart';

    /**
     * @var Data
     */
    private $abandonedCartData;

    /**
     * @param Data $abandonedCartData
     */
    public function __construct(Data $abandonedCartData)
    {
        $this->abandonedCartData = $abandonedCartData;
    }

    /**
     * @param FrontControllerInterface $subject
     * @param RequestInterface $request
     *
     * @return void
     */
    public function beforeDispatch(FrontControllerInterface $subject, $request)
    {
        if ($this->abandonedCartData->isEnabled()
            && strpos($request->getPathInfo(), self::ABANDONED_CART_PATH) !== false
        ) {
            $request->setMethod('POST');
        }
    }
}
