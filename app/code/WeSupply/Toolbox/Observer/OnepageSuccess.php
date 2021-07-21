<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use WeSupply\Toolbox\Api\OrderRepositoryInterface;
use WeSupply\Toolbox\Logger\Logger;

/**
 * Class OnepageSuccess
 *
 * @package WeSupply\Toolbox\Observer
 */
class OnepageSuccess implements ObserverInterface
{

    /**
     * @var OrderRepositoryInterface
     */
    protected $weSupplyOrderRepository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * OnepageSuccess constructor.
     *
     * @param OrderRepositoryInterface $weSupplyOrderRepository
     * @param Logger                   $logger
     */
    public function __construct(
        OrderRepositoryInterface $weSupplyOrderRepository,
        Logger $logger
    )
    {
        $this->weSupplyOrderRepository = $weSupplyOrderRepository;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return OnepageSuccess
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getDataByKey('order');

        $this->refreshUpdatedAt($order);

        return $this;
    }

    /**
     * @param $order
     */
    private function refreshUpdatedAt($order)
    {
        try {
            $wsOrder = $this->weSupplyOrderRepository->getByOrderId($order->getId());
            $wsOrder->setUpdatedAt(
                $order->getUpdatedAt()
            )->save();
        } catch (\Exception $ex) {
            $this->logger->error('WeSupply order with ID' . $order->getId() . ' not found. Error::' . $ex->getMessage());
        }
    }
}
