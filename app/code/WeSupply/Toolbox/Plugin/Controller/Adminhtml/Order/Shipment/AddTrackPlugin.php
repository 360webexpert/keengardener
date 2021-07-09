<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Plugin\Controller\Adminhtml\Order\Shipment;

use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\AddTrack;
use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;
use WeSupply\Toolbox\Logger\Logger as WeSupplyLogger;

/**
 * Class AddTrackPlugin
 * @package WeSupply\Toolbox\Plugin\Controller\Adminhtml\Order\Shipment
 */
class AddTrackPlugin
{
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var WeSupplyHelper
     */
    protected $helper;

    /**
     * @var WeSupplyLogger
     */
    protected $logger;

    /**
     * AddTrackPlugin constructor.
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ManagerInterface $eventManager
     * @param WeSupplyHelper $helper
     * @param WeSupplyLogger $logger
     */
    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        ManagerInterface $eventManager,
        WeSupplyHelper $helper,
        WeSupplyLogger $logger
    )
    {
        $this->shipmentRepository = $shipmentRepository;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param AddTrack $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute(AddTrack $subject, \Closure $proceed)
    {
        $origProceed = $proceed();
        $shipmentId = $subject->getRequest()->getParam('shipment_id');

        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
            if ($shipment->getId()) {
                $orderId = $shipment->getOrderId();
                if ($orderId && $this->helper->getWeSupplyEnabled()) {
                    $this->eventManager->dispatch(
                        'wesupply_order_update',
                        ['orderId' => $orderId]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(__('Shipment with ID %1 does not exist. %2', $shipmentId, $e->getMessage()));
        }

        return $origProceed;
    }
}