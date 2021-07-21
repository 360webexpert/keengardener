<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Cron\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use WeSupply\Toolbox\Api\OrderRepositoryInterface;
use WeSupply\Toolbox\Cron\CronBase;
use WeSupply\Toolbox\Helper\WeSupplyMappings;
use WeSupply\Toolbox\Logger\Logger;

/**
 * Class OrderUpdatesCheck
 *
 * @package WeSupply\Toolbox\Cron
 */
class UpdatesCheck extends CronBase
{
    /**
     * @var string
     */
    private const DATETIME_OFFSET = '-10 minutes';

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * OrderUpdatesCheck constructor.
     *
     * @param Context                  $context
     * @param DateTime                 $dateTime
     * @param OrderRepositoryInterface $wsOrderRepository
     * @param CollectionFactory        $orderCollectionFactory
     * @param WeSupplyMappings         $weSupplyMappings
     * @param Json                     $json
     * @param Logger                   $logger
     */
    public function __construct (
        Context $context,
        DateTime $dateTime,
        OrderRepositoryInterface $wsOrderRepository,
        CollectionFactory $orderCollectionFactory,
        WeSupplyMappings $weSupplyMappings,
        Json $json,
        Logger $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;

        parent::__construct(
            $context,
            $dateTime,
            $wsOrderRepository,
            $weSupplyMappings,
            $json,
            $logger
        );
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $orders = $this->getOrderCollectionByDate();
        if (!$orders) {
            return $this;
        }

        $updated = $orders->getItems();
        if (empty($updated)) {
            return $this;
        }

        $this->compareUpdatedAt($updated);

        return $this;
    }

    /**
     * Get orders collection filtered by data range
     *
     * @return Collection
     */
    private function getOrderCollectionByDate()
    {
        $currDateTime = $this->getCurrentTimestamp();
        $startDateTime = $this->formatDateTime(strtotime(self::DATETIME_OFFSET, $currDateTime));

        return $this->orderCollectionFactory->create()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('updated_at')
            ->addFieldToFilter('updated_at', ['gteq' => $startDateTime])
            ->setOrder('updated_at','asc');
    }

    /**
     * Compare orders' updated_at attr
     *
     * @param $updatedOrders
     */
    private function compareUpdatedAt($updatedOrders)
    {
        foreach ($updatedOrders as $orderId => $order) {
            $wsOrder = $this->wsOrderRepository->getByOrderId($orderId);
            if (!$wsOrder->getId()) {
                continue;
            }

            if (
                $this->dateTime->timestamp($order->getUpdatedAt()) >
                $this->dateTime->timestamp($wsOrder->getUpdatedAt())
            ) {
                // set update_awaiting flag
                $wsOrder->setAwaitingUpdate(TRUE)->save();
            }
        }
    }
}
