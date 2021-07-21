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
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use WeSupply\Toolbox\Api\OrderRepositoryInterface;
use WeSupply\Toolbox\Cron\CronBase;
use WeSupply\Toolbox\Helper\WeSupplyMappings;
use WeSupply\Toolbox\Logger\Logger;

/**
 * Class OrdersDelete
 *
 * @package WeSupply\Toolbox\Cron
 */
class MassDelete extends CronBase
{
    /**
     * @var string
     */
    private const WS_TABLE_NAME = 'wesupply_orders';

    /**
     * @var string
     */
    private const DATETIME_OFFSET = '-6 months';

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
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * OrdersDelete constructor.
     *
     * @param Context                  $context
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param SortOrderBuilder         $sortOrderBuilder
     * @param OrderRepositoryInterface $wsOrderRepository
     * @param ResourceConnection       $resourceConnection
     * @param WeSupplyMappings         $weSupplyMappings
     * @param DateTime                 $dateTime
     * @param Json                     $json
     * @param Logger                   $logger
     */
    public function __construct
    (
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        OrderRepositoryInterface $wsOrderRepository,
        ResourceConnection $resourceConnection,
        WeSupplyMappings $weSupplyMappings,
        DateTime $dateTime,
        Json $json,
        Logger $logger
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->resourceConnection = $resourceConnection;

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
        try {
            $this->prepareSearchCriteria();
            $orders = $this->wsOrderRepository->getList(
                $this->searchCriteriaBuilder->create()
            )->getItems();
        } catch (\Exception $ex) {
            $this->logger->error('OrdersDelete error :: ' . $ex->getMessage());
        }

        $this->filterOrdersByFinalStatuses($orders);
        $this->filterOrdersByLimit($orders);

        $completeOrderIds = array_keys($orders);
        if (!empty($completeOrderIds) && $this->massDeleteWeSupplyOrders($completeOrderIds)) {
            $orderIncrementIds = array_column(
                array_column($orders, '_data'),
                'order_number'
            );

            return $this;
        }

        return $this;
    }

    /**
     * Add order filter
     */
    private function prepareSearchCriteria()
    {
        $endDate = $this->formatDateTime(
            strtotime(self::DATETIME_OFFSET, $this->getCurrentTimestamp())
        );

        $this->sortOrderBuilder->setDirection(SortOrder::SORT_ASC);
        $this->sortOrderBuilder->setField('id');
        $sortOrder = $this->sortOrderBuilder->create();

        $this->searchCriteriaBuilder
            ->addFilter('updated_at', $endDate, 'lteq')
            ->addSortOrder($sortOrder);
    }

    /**
     * @param $orders
     */
    private function filterOrdersByFinalStatuses(&$orders)
    {
        $orders = array_filter($orders, function($order) {

            libxml_use_internal_errors(true);
            $infoXml = simplexml_load_string($order->getInfo());

            if (FALSE === $infoXml) {
                $this->logger->error(
                    'Invalid XML on order ID ' .
                    $order->getOrderId() . ' | ' . $order->getOrderNumber(),
                    libxml_get_errors()
                );

                return FALSE;
            }

            $orderData = $this->json->unserialize(
                $this->json->serialize((array) $infoXml)
            );

            return in_array($orderData['OrderStatusId'], $this->weSupplyMappings::ORDER_FINAL_STATUS_IDS);
        });
    }

    /**
     * @param $orders
     */
    private function filterOrdersByLimit(&$orders)
    {
        $orders = array_slice($orders, 0, self::ORDERS_LIMIT, TRUE);
    }

    /**
     * @param $completeOrderIds
     *
     * @return int
     */
    private function massDeleteWeSupplyOrders($completeOrderIds)
    {
        $connection  = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName(self::WS_TABLE_NAME);

        $whereConditions = [
            $connection->quoteInto('id IN(?)', $completeOrderIds)
        ];

         return $connection->delete($tableName, $whereConditions);
    }
}
