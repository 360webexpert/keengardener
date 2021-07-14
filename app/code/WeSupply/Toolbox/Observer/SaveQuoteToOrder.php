<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

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
     * SaveQuoteToOrder constructor.
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TimezoneInterface $timezone
    )
    {
        $this->timezone = $timezone;
    }

    /**
     * Saves data get from WeSupply estimates api
     *
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getDeliveryTimestamp()) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $order->setData('delivery_timestamp', $quote->getDeliveryTimestamp());
        $order->setData('delivery_utc_offset', $this->timezone->scopeDate()->getOffset());

        return $this;
    }
}
