<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Backend\Block\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\OrderRepository;

/**
 * grid block action item renderer
 */
class OrderId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Number
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * OrderId constructor.
     * @param Context $context
     * @param OrderRepository $orderRepository
     * @param Logger $suiteLogger
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderRepository $orderRepository,
        Logger $suiteLogger,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->suiteLogger = $suiteLogger;
    }

    /**
     * Render grid row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $orderId = parent::render($row);

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $exception->getMessage());
            return '';
        } catch (InputException $exception) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $exception->getMessage());
            return '';
        }

        $link = $this->getUrl('sales/order/view/', ['order_id' => $order->getEntityId()]);

        return '<a href="' . $link . '">' . $order->getIncrementId() . '</a>';
    }
}
