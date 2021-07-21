<?php
namespace WeSupply\Toolbox\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderRepositoryInterface;
use WeSupply\Toolbox\Api\OrderInfoBuilderInterface;
use WeSupply\Toolbox\Api\OrderRepositoryInterface as WeSupplyOrderRepositoryInterface;
use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;
use WeSupply\Toolbox\Logger\Logger as Logger;

class UpdateOrderInfo implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepositoryInterface;

    /**
     * @var WeSupplyOrderRepositoryInterface
     */
    protected $weSupplyOrderRepository;

    /**
     * @var OrderInfoBuilderInterface
     */
    protected $orderInfoBuilder;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var WeSupplyHelper
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * UpdateOrderInfo constructor.
     *
     * @param OrderRepositoryInterface         $orderRepositoryInterface
     * @param WeSupplyOrderRepositoryInterface $weSupplyOrderRepository
     * @param OrderInfoBuilderInterface        $orderInfoBuilder
     * @param DateTime                         $dateTime
     * @param Http                             $request
     * @param Json                             $json
     * @param WeSupplyHelper                   $helper
     * @param Logger                           $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepositoryInterface,
        WeSupplyOrderRepositoryInterface $weSupplyOrderRepository,
        OrderInfoBuilderInterface $orderInfoBuilder,
        DateTime $dateTime,
        Http $request,
        Json $json,
        WeSupplyHelper $helper,
        Logger $logger
    )
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->weSupplyOrderRepository = $weSupplyOrderRepository;
        $this->orderInfoBuilder = $orderInfoBuilder;
        $this->dateTime = $dateTime;
        $this->request = $request;
        $this->json = $json;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $orderId = $observer->getData('orderId');

        try {
            $order = $this->orderRepositoryInterface->get($orderId);
        } catch (\Exception $ex) {
            $this->logger->error("WeSupply Error: Order with id $orderId not found. Error: " . $ex->getMessage());
            return $this;
        }

        try {
            $weSupplyOrder = $this->weSupplyOrderRepository->getByOrderId($orderId);

            /** check if should skip from import the order that was just placed  */
            if ($this->helper->shouldIgnoreOrder($order, $weSupplyOrder)) {
                return $this;
            }

            /** check if should skip from update already placed order */
            if ($this->request->getFrontName() === 'admin' /** check only for updates from magento admin */
                && !$weSupplyOrder->getId() && (
                    /** in case of pending order update for order that was excluded at the time when it was placed */
                    ($order->getStatus() === 'pending' && $order->getExcludeImportPending()) ||
                    /** in case of complete order update for order that was excluded at the time when it was placed */
                    ($order->getStatus() === 'complete' && $order->getExcludeImportComplete())
                    /** backwards compatibility for order already saved in the past and excluded at the time when it was placed  */
                    || $weSupplyOrder->isExcluded()
                )
            ) {
                return $this;
            }

            $existingOrderXml = simplexml_load_string($weSupplyOrder->getInfo(), 'SimpleXMLElement');
            $jsonOrderData = $this->json->serialize($existingOrderXml);
            $existingOrderData = $this->json->unserialize($jsonOrderData);

            /** check if should skip from re-import the order that was deleted by cron */
            $timeDateOffset = $this->dateTime->date(
                'Y-m-d H:i:s', strtotime(
                    '-6 months', $this->dateTime->gmtTimestamp()
                )
            );
            if (empty($existingOrderData) && $order->getCreatedAt() < $timeDateOffset) {
                return $this;
            }

            /** build order data */
            $orderData = $this->orderInfoBuilder->gatherInfo($order, $existingOrderData ?? null);

            /** check order data and skip empty order */
            if (empty($orderData)) {
                $this->logger->error("WeSupply Error: OrderInfoBuilder failed for order id $orderId");
                return $this;
            }

            $orderInfo = $this->orderInfoBuilder->prepareForStorage($orderData);

            $weSupplyOrder->setOrderId($orderId);
            $weSupplyOrder->setOrderNumber($this->orderInfoBuilder->getOrderNumber($orderData));
            $weSupplyOrder->setStoreId($this->orderInfoBuilder->getStoreId($orderData));
            $weSupplyOrder->setInfo($orderInfo);

            $this->weSupplyOrderRepository->save($weSupplyOrder);

        } catch (\Exception $ex) {
            $this->logger->error("WeSupply Error: " . $ex->getMessage());
        }

        return $this;
    }
}
