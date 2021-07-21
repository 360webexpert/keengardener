<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;

/**
 * Class SaveQuoteToOrder
 * @package WeSupply\Toolbox\Observer\Estimations
 */
class SaveQuoteToOrder implements ObserverInterface
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var WeSupplyHelper
     */
    protected $wsHelper;

    /**
     * SaveQuoteToOrder constructor.
     *
     * @param TimezoneInterface $timezone
     * @param WeSupplyHelper    $wsHelper
     */
    public function __construct(
        TimezoneInterface $timezone,
        WeSupplyHelper $wsHelper
    )
    {
        $this->timezone = $timezone;
        $this->wsHelper = $wsHelper;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $order->setData('exclude_import_pending', $this->wsHelper->excludePendingOrders());
        $order->setData('exclude_import_complete', $this->wsHelper->excludeCompleteOrders());

        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getDeliveryTimestamp()) {
            return $this;
        }

        $order->setData('delivery_timestamp', $quote->getDeliveryTimestamp());
        $order->setData('delivery_utc_offset', $this->timezone->scopeDate()->getOffset());

        return $this;
    }
}
