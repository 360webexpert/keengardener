<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Block\Estimations;

use Magento\Checkout\Block\Onepage\Success;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;

/**
 * Class DisplayEta
 * @package WeSupply\Toolbox\Block\Estimations
 */
class DisplayEta extends Success
{
    /**
     * @var WeSupplyHelper
     */
    private $helper;

    /**
     * DisplayEta constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param Config $orderConfig
     * @param HttpContext $httpContext
     * @param WeSupplyHelper $helper
     * @param array $data.
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $orderConfig,
        HttpContext $httpContext,
        WeSupplyHelper $helper,
        array $data = []
    )
    {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);

        $this->helper = $helper;
    }

    /**
     * Get saved delivery timestamp from order
     * and prepare it to be displayed on page
     * @return bool|string
     */
    public function getEstimatedDelivery()
    {
        $estimatedDelivery = [];
        $order = $this->getOrder();

        if ($order->getId() && $estimatedTimestamp = $order->getDeliveryTimestamp()) {
            $estimatedTimestamp = explode(',', $estimatedTimestamp);
            $estimationsFormat = $this->helper->getDeliveryEstimationsFormat();
            $prevTimestamp = false;
            foreach ($estimatedTimestamp as $timestamp) {
                $currentTimestamp = $timestamp;
                if ($prevTimestamp && $currentTimestamp == $prevTimestamp) {
                    continue;
                }

                $estimatedDelivery[] = date($estimationsFormat, $timestamp);
                $prevTimestamp = $currentTimestamp;
            }
        }

        return $estimatedDelivery ? implode(' - ', $estimatedDelivery) : false;
    }

    /**
     * @return Order
     */
    protected function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }
}