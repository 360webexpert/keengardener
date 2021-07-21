<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Cron;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use WeSupply\Toolbox\Api\OrderRepositoryInterface;
use WeSupply\Toolbox\Helper\WeSupplyMappings;
use WeSupply\Toolbox\Logger\Logger;

/**
 * Class CronBase
 *
 * @package WeSupply\Toolbox\Cron
 */
class CronBase extends Template
{
    /**
     * @var string
     */
    protected const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var OrderRepositoryInterface
     */
    protected $wsOrderRepository;

    /**
     * @var WeSupplyMappings
     */
    protected $weSupplyMappings;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * CronBase constructor.
     *
     * @param Context                  $context
     * @param DateTime                 $dateTime
     * @param OrderRepositoryInterface $wsOrderRepository
     * @param WeSupplyMappings         $weSupplyMappings
     * @param Json                     $json
     * @param Logger                   $logger
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        OrderRepositoryInterface $wsOrderRepository,
        WeSupplyMappings $weSupplyMappings,
        Json $json,
        Logger $logger
    )
    {
        $this->dateTime = $dateTime;
        $this->wsOrderRepository = $wsOrderRepository;
        $this->weSupplyMappings = $weSupplyMappings;
        $this->json = $json;
        $this->logger = $logger;

        parent::__construct($context, []);
    }

    /**
     * @return false|int
     */
    protected function getCurrentTimestamp()
    {
        return $this->dateTime->gmtTimestamp();
    }

    /**
     * @param string $timestamp
     *
     * @return string
     */
    protected function formatDateTime($timestamp = '')
    {
        if (empty($timestamp)) {
            $timestamp = $this->getCurrentTimestamp();
        }

        return $this->dateTime->date(self::DATETIME_FORMAT, $timestamp);
    }
}
