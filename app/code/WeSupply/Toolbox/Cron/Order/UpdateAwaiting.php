<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Cron\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use WeSupply\Toolbox\Api\OrderRepositoryInterface;
use WeSupply\Toolbox\Cron\CronBase;
use WeSupply\Toolbox\Helper\WeSupplyMappings;
use WeSupply\Toolbox\Logger\Logger;

/**
 * Class UpdateAwaiting
 *
 * @package WeSupply\Toolbox\Cron\Order
 */
class UpdateAwaiting extends CronBase
{
    /**
     * @var int
     */
    private const ORDERS_LIMIT = 500;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * UpdateAwaiting constructor.
     *
     * @param Context                  $context
     * @param DateTime                 $dateTime
     * @param OrderRepositoryInterface $wsOrderRepository
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param SortOrderBuilder         $sortOrderBuilder
     * @param WeSupplyMappings         $weSupplyMappings
     * @param Json                     $json
     * @param Logger                   $logger
     */
    public function __construct (
        Context $context,
        DateTime $dateTime,
        OrderRepositoryInterface $wsOrderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        WeSupplyMappings $weSupplyMappings,
        Json $json,
        Logger $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;

        parent::__construct(
            $context,
            $dateTime,
            $wsOrderRepository,
            $weSupplyMappings,
            $json,
            $logger
        );
    }

    public function execute()
    {
        try {
            $this->prepareSearchCriteria();
            $wsOrders = $this->wsOrderRepository->getList(
                $this->searchCriteriaBuilder->create()
            )->getItems();
        } catch (\Exception $ex) {
            $this->logger->error('UpdateAwaiting error :: ' . $ex->getMessage());
        }

        $this->filterOrdersByLimit($wsOrders);
        if (!empty($wsOrders)) {
            foreach ($wsOrders as $wsOrder) {
                $wsOrder->setAwaitingUpdate(FALSE)->save();
                $this->wsOrderRepository->setOrder($wsOrder);
                $this->wsOrderRepository->triggerOrderUpdate();
            }

            return $this;
        }

        return $this;
    }

    /**
     * Add order filter
     */
    private function prepareSearchCriteria()
    {
        $this->sortOrderBuilder->setDirection(SortOrder::SORT_ASC);
        $this->sortOrderBuilder->setField('id');
        $sortOrder = $this->sortOrderBuilder->create();

        $this->searchCriteriaBuilder
            ->addFilter('awaiting_update', TRUE, 'eg')
            ->addSortOrder($sortOrder);
    }

    /**
     * @param $wsOrders
     */
    private function filterOrdersByLimit(&$wsOrders)
    {
        $wsOrders = array_slice($wsOrders, 0, self::ORDERS_LIMIT, TRUE);
    }
}
