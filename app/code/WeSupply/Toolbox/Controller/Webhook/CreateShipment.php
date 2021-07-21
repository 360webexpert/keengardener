<?php

namespace WeSupply\Toolbox\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item;

class CreateShipment extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var OrderItemInterface
     */
    private $orderItemRepository;

    /**
     * CreateShipment constructor.
     *
     * @param Context     $context
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ObjectManagerInterface $objectManager,
        OrderItemInterface $orderItemRepository
    )
    {
        $this->resultJsonFactory = $jsonFactory;
        $this->_objectManager = $objectManager;
        $this->orderItemRepository = $orderItemRepository;

        parent::__construct($context);
    }

    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $params = $this->_objectManager->create('Magento\Framework\App\RequestInterface')->getParams();
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($params['orderId']);

        if (!$order->canShip()) {
            throw new LocalizedException(__('You can\'t create shipment for this order (already shipped).'));
        }

        $qtyToShip = $params['itemQty'];
        $itemToShip = $this->_objectManager->create('Magento\Sales\Model\Order\Item')->load($params['itemId']);

        $sourceCode = 'default';
        $productSku = $itemToShip->getProduct()->getSku();
        $inventorySource = $this->_objectManager->create('Magento\InventoryApi\Api\GetSourceItemsBySkuInterface')
            ->execute($productSku);

        foreach ($inventorySource as $source) {
            if ($source->getQuantity() > 0) {
                $sourceCode = $source->getSourceCode();
            }
        }

        $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
        $shipmentItem = $convertOrder->itemToShipmentItem($itemToShip)->setQty($qtyToShip);

        // Check if order item has qty to ship or is virtual
        if (!$itemToShip->getQtyToShip() || $itemToShip->getIsVirtual()) {
            throw new LocalizedException(__('You can\'t create shipment for this item (already shipped).'));
        }

        $shipment = $convertOrder->toShipment($order);
        $shipment->addItem($shipmentItem);

        // Register shipment
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);

        $shipment->getExtensionAttributes()->setSourceCode($sourceCode);

        $track = $this->_objectManager->create('Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory')->create()
            ->addData([
                'carrier_code' => $params['carrierCode'],
                'title' => $params['carrierTitle'],
                'number' => $params['trackingNumber'],
            ]);

        $shipment->addTrack($track);

        try {
            // Save created shipment and order
            $shipment->save();
            $shipment->getOrder()->save();

            return $resultJson->setData([
                'success' => TRUE,
                'shipment id' => $shipment->getIncrementId()
            ]);

        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
