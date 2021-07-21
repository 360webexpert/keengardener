<?php

namespace Ebizmarts\SagePaySuite\Model\ObjectLoader;

use Ebizmarts\SagePaySuite\Helper\RepositoryQuery;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;

class OrderLoader
{
    /** @var OrderRepository */
    private $orderRepository;

    /** @var RepositoryQuery */
    private $repositoryQuery;

    /**
     * OrderLoader constructor.
     * @param OrderRepository $orderRepository
     * @param RepositoryQuery $repositoryQuery
     */
    public function __construct(
        OrderRepository $orderRepository,
        RepositoryQuery $repositoryQuery
    ) {
        $this->orderRepository = $orderRepository;
        $this->repositoryQuery = $repositoryQuery;
    }

    /**
     * @param Quote $quote
     * @return \Magento\Sales\Model\Order
     * @throws LocalizedException
     */
    public function loadOrderFromQuote(Quote $quote)
    {
        $searchCriteria = $this->createSearchCriteria($quote);

        /** @var Order */
        $order = null;
        $orders = $this->orderRepository->getList($searchCriteria);
        $ordersCount = $orders->getTotalCount();

        if ($ordersCount > 0) {
            $orders = $orders->getItems();
            $order = current($orders);
        }

        if ($order === null || $order->getId() === null) {
            throw new LocalizedException(__("Invalid order."));
        }

        return $order;
    }

    /**
     * @param Quote $quote
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function createSearchCriteria(Quote $quote)
    {
        $incrementId = $quote->getReservedOrderId();
        $storeId = $quote->getStoreId();

        $incrementIdFilter = [
            'field' => 'increment_id',
            'conditionType' => 'eq',
            'value' => $incrementId
        ];
        $storeIdFilter = [
            'field' => 'store_id',
            'conditionType' => 'eq',
            'value' => $storeId
        ];

        $searchCriteria = $this->repositoryQuery->buildSearchCriteriaWithAND([$incrementIdFilter, $storeIdFilter]);
        return $searchCriteria;
    }
}
