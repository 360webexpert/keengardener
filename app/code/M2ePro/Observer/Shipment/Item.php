<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Observer\Shipment;

class Item extends \Ess\M2ePro\Observer\AbstractModel
{
    //########################################

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function process()
    {
        if ($this->getHelper('Data\GlobalData')->getValue('skip_shipment_observer')) {
            return;
        }

        /** @var $shipmentItem \Magento\Sales\Model\Order\Shipment\Item */
        $shipmentItem = $this->getEvent()->getShipmentItem();
        $shipment = $shipmentItem->getShipment();

        /**
         * We can catch two the same events: save of \Magento\Sales\Model\Order\Shipment\Item and
         * \Magento\Sales\Model\Order\Shipment\Track. So we must skip a duplicated one.
         */
        $objectHash = spl_object_hash($shipment->getTracksCollection()->getLastItem());
        $eventKey = 'skip_' . $shipment->getId() .'##'. $objectHash;
        if (!$this->getHelper('Data_GlobalData')->getValue($eventKey)) {
            $this->getHelper('Data_GlobalData')->setValue($eventKey, true);
        }

        $magentoOrderId = $shipment->getOrderId();

        try {
            /** @var $order \Ess\M2ePro\Model\Order */
            $order = $this->activeRecordFactory->getObjectLoaded('Order', $magentoOrderId, 'magento_order_id');
        } catch (\Exception $e) {
            return;
        }

        if ($order === null) {
            return;
        }

        if (!in_array($order->getComponentMode(), $this->getHelper('Component')->getEnabledComponents())) {
            return;
        }

        $order->getLog()->setInitiator(\Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION);

        /** @var $shipmentHandler \Ess\M2ePro\Model\Order\Shipment\Handler */
        $componentMode = ucfirst($order->getComponentMode());
        $shipmentHandler = $this->modelFactory->getObject("{$componentMode}_Order_Shipment_Handler");
        $shipmentHandler->handleItem($order, $shipmentItem);
    }

    //########################################
}
