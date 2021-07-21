<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order;
use Magento\Sales\Model\Order as SalesOrderModel;
use Magento\Shipping\Model\ShipmentNotifier;
use WeSupply\Toolbox\Logger\Logger as Logger;
use WeSupply\Toolbox\Model\Webhook;

/**
 * Class Pickup
 *
 * @package WeSupply\Toolbox\Controller\Webhook
 */
class Pickup extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    /**
     * @var array
     */
    protected $params;
    /**
     * @var Webhook
     */

    private $webhook;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Order
     */
    protected $convertOrder;

    /**
     * @var ShipmentNotifier
     */
    protected $shipmentNotifier;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var
     */
    private $order;

    /**
     * @var string
     */
    private $finalErrorMessage;

    /**
     * @var array
     */
    private $itemQtyPairs;


    /**
     * Pickup constructor.
     *
     * @param Context                  $context
     * @param JsonFactory              $jsonFactory
     * @param JsonSerializer           $jsonSerializer
     * @param Webhook                  $webhook
     * @param Logger                   $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param Order                    $convertOrder
     * @param ShipmentNotifier         $shipmentNotifier
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        JsonSerializer $jsonSerializer,
        Webhook $webhook,
        Logger $logger,
        OrderRepositoryInterface $orderRepository,
        Order $convertOrder,
        ShipmentNotifier $shipmentNotifier
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->webhook = $webhook;
        $this->logger = $logger;

        $this->orderRepository = $orderRepository;
        $this->convertOrder = $convertOrder;
        $this->shipmentNotifier = $shipmentNotifier;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $this->params = $this->getRequest()->getParams();

        if (!$this->requestIsAllowed()) {
            $error = $this->webhook->getError();
            $this->logger->addError($error['status-message']);

            return $resultJson->setData($error);
        }

        $this->getOrder();
        if (!$this->order) {
            $this->pushErrorMessage(
                'Cannot create Magento package. Order with ID ' . $this->orderId . ' was not found!'
            );
            $this->logger->addError($this->finalErrorMessage);

            return $resultJson->setData([
                'success' => false,
                'status-title' => 'Magento package update failed!',
                'status-message' => $this->finalErrorMessage
            ]);
        }

        switch ($this->params['action']) {
            case 'ready_for_pickup':
                $updateResp = $this->shipmentUpdate();
                break;
            case 'canceled_orders':
                $updateResp = $this->cancelOrder();
                break;
            default:
                $this->pushErrorMessage('Update action does not exist ' . $this->params['action']);
                $this->logger->addError($this->finalErrorMessage);

                $updateResp = [
                    'success' => false,
                    'status-title' => 'Magento package update failed!',
                    'status-message' => $this->finalErrorMessage
                ];
        }

        return $resultJson->setData($updateResp);
    }

    /**
     * @return bool
     */
    private function requestIsAllowed(): bool
    {
        if (
            !$this->webhook->canProceedsRequest() ||
            !$this->webhook->validateParams('pickup', $this->params)
        ) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Get order by provided id
     */
    private function getOrder()
    {
        $this->orderId = $this->webhook->prepareOrderId($this->params['order_id']);
        try {
            $this->order = $this->orderRepository->get($this->orderId);
        } catch (\Exception $e) {
            $this->pushErrorMessage($e->getMessage());
        }
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function shipmentUpdate()
    {
        if (!$this->checkOrderCanShip()) {
            return [
                'success' => false,
                'status-title' => 'Magento package update failed!',
                'status-message' => $this->finalErrorMessage
            ];
        }

        $itemsData = $this->prepareItems();
        if (empty($itemsData)) {
            return [
                'success' => false,
                'status-title' => 'Magento package update failed!',
                'status-message' => $this->finalErrorMessage
            ];
        }

        $orderShipment = $this->convertOrder->toShipment($this->order);
        foreach ($itemsData as $data) {
            $shipmentItem = $this->convertOrder
                ->itemToShipmentItem($data['item'])
                ->setQty($data['qty']);

            $orderShipment->addItem($shipmentItem);
        }

        $orderShipment->register();
        $orderShipment->getOrder()->setIsInProcess(true);

        if ($this->isMsiSetup()) {
            $orderShipment->getExtensionAttributes()
                ->setSourceCode($this->params['pickup_store_id']);
        }

        try {
            $orderShipment->save();
            $orderShipment->getOrder()->save();
        } catch (\Exception $e) {
            $this->pushErrorMessage($e->getMessage());

            return [
                'success' => false,
                'status-title' => 'Magento package update failed!',
                'status-message' => $this->finalErrorMessage
            ];
        }

        return [
            'success' => true,
            'status-title' => 'Magento package successfully updated.',
            'status-message' => 'Created package #' . $orderShipment->getIncrementId() . ' for order #' . $this->order->getIncrementId()
        ];
    }

    /**
     * @return array
     */
    private function cancelOrder()
    {
        if (!$this->checkOrderCanCancel()) {
            return [
                'success' => false,
                'status-title' => 'Magento package update failed!',
                'status-message' => $this->finalErrorMessage
            ];
        }

        $itemsData = $this->prepareItems();
        if (empty($itemsData)) {
            return [
                'success' => false,
                'status-title' => 'Magento package update failed!',
                'status-message' => $this->finalErrorMessage
            ];
        }

        foreach ($itemsData as $data) {
            $item = $data['item'];
            $item
                ->setQtyCanceled($data['qty'] + $item->getQtycanceled())
                ->save();
        }

        try {
            if ($this->allItemsAreCanceled()) {
                $completeFlag = $this->order->getConfig()->getStateDefaultStatus(SalesOrderModel::STATE_CANCELED);
                $this->order->setState($completeFlag);
                $this->order->setStatus($completeFlag);
            }
            $this->order->save();
        } catch (\Exception $e) {
            $this->pushErrorMessage($e->getMessage());

            return [
                'success' => false,
                'status-title' => 'Magento package update failed!',
                'status-message' => $this->finalErrorMessage
            ];
        }

        $statusMsg = count($this->itemQtyPairs) > 1 ?
            'The items were canceled.' : 'The item was canceled.';

        return [
            'success' => true,
            'status-title' => 'Magento package successfully updated.',
            'status-message' => $statusMsg
        ];
    }

    /**
     * @return bool
     */
    private function pairItemsAndQuantities()
    {
        if (empty($this->params['item_ids']) || empty($this->params['item_quantities'])) {
            $this->pushErrorMessage('Item ids and/or quantities cannot be empty.');

            return FALSE;
        }

        $itemIdsArr = explode(';', $this->params['item_ids']);
        $itemsQuantitiesArr = explode(';', $this->params['item_quantities']);

        if (count($itemIdsArr) !== count($itemsQuantitiesArr)) {
            $this->pushErrorMessage('Item ids and quantities cannot be paired.');
            $this->pushErrorMessage('The number of item_ids should match the number of item_quantities.');

            return FALSE;
        }

        foreach ($itemIdsArr as $key => $id) {
            $this->itemQtyPairs[$id] = $itemsQuantitiesArr[$key];
        }

        return TRUE;
    }

    /**
     * @return bool
     */
    private function isMsiSetup()
    {
        if ($extAttrs = $this->order->getExtensionAttributes()) {
            $extAttrsArr = $extAttrs->__toArray();
            if (!isset($extAttrsArr['pickup_location_code'])) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * @return bool
     */
    private function checkOrderCanShip()
    {
        if (!$this->order->canShip()) {
            $this->pushErrorMessage(
                'Cannot update Magento package. Check Magento order #' . $this->order->getIncrementId()
            );
            $this->logger->addError($this->finalErrorMessage);

            return FALSE;
        }

        return TRUE;
    }

    /**
     * @return bool
     */
    private function checkOrderCanCancel()
    {
        if (!$this->order->canCancel()) {
            $this->pushErrorMessage(
                'Cannot cancel Magento package. Check Magento order #' . $this->order->getIncrementId()
            );
            $this->logger->addError($this->finalErrorMessage);

            return FALSE;
        }

        return TRUE;
    }

    /**
     * @return array
     */
    private function prepareItems()
    {
        $processedItems = [];
        if (FALSE === $this->pairItemsAndQuantities()) {
            return $processedItems;
        }

        $itemIdsToProcess = array_keys($this->itemQtyPairs);
        $allItems = $this->order->getAllItems();
        foreach ($allItems as $item) {
            if (!in_array($item->getId(), $itemIdsToProcess) || $item->getIsVirtual()) {
                continue;
            }

            $qtyToProcess = $this->itemQtyPairs[$item->getId()];
            $pendingQty = $this->getPendingItemsQty($item);
            if ($pendingQty < $qtyToProcess) {
                $this->pushErrorMessage('Invalid quantity for item ID ' . $item->getId() . ' | ' . $item->getName());
                $this->pushErrorMessage(
                    'Maximum quantity allowed is ' . $pendingQty . ' Check Magento order #' . $this->order->getIncrementId()
                );

                continue;
            }

            $processedItems[$item->getId()] = [
                'item' => $item,
                'qty'  => $qtyToProcess
            ];
        }

        return $processedItems;
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    private function getPendingItemsQty($item)
    {
        return
            $item->getQtyOrdered()
            - $item->getQtyRefunded()
            - $item->getQtyCanceled();
    }

    /**
     * @return bool
     */
    private function allItemsAreCanceled()
    {
        $orderedItems = $canceledItems = 0;
        $allItems = $this->order->getAllVisibleItems();
        foreach ($allItems as $item) {
            if ($item->getIsVirtual()) {
                continue;
            }
            $orderedItems += $item->getQtyOrdered();
            $canceledItems += $item->getQtyCanceled();
        }

        return $orderedItems === $canceledItems;
    }

    /**
     * @param $message
     */
    private function pushErrorMessage($message)
    {
        $this->finalErrorMessage .= $message . ' ';
    }
}
