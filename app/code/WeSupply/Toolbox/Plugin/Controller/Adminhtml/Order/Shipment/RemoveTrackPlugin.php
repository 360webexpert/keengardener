<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Plugin\Controller\Adminhtml\Order\Shipment;

use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\RemoveTrack;
use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;
use WeSupply\Toolbox\Logger\Logger as WeSupplyLogger;

/**
 * Class RemoveTrackPlugin
 * @package WeSupply\Toolbox\Plugin\Model\Controller\Adminhtml\Order\Shipment
 */
class RemoveTrackPlugin
{
    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

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
     * RemoveTrackPlugin constructor.
     * @param ShipmentLoader $shipmentLoader
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $eventManager
     * @param WeSupplyHelper $helper
     * @param WeSupplyLogger $logger
     */
    public function __construct(
        ShipmentLoader $shipmentLoader,
        ObjectManagerInterface $objectManager,
        ManagerInterface $eventManager,
        WeSupplyHelper $helper,
        WeSupplyLogger $logger
    )
    {
        $this->shipmentLoader = $shipmentLoader;
        $this->_objectManager = $objectManager;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param RemoveTrack $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute(RemoveTrack $subject, \Closure $proceed)
    {
        /** @var Track $track */
        $trackId = $subject->getRequest()->getParam('track_id');
        $track = $this->_objectManager->create(Track::class)->load($trackId);
        $origProceed = $proceed();

        if (!$track->getId()) {
            $this->logger->error(__('Track with ID %1 does not exist.', $trackId));

            return $origProceed;
        }

        $orderId = $track->getOrderId();
        if ($orderId && $this->helper->getWeSupplyEnabled()) {
            $this->eventManager->dispatch(
                'wesupply_order_update',
                ['orderId' => $orderId]
            );
        }

        return $origProceed;
    }
}