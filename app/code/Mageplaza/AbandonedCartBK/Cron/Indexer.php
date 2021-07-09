<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Cron;

use DateInterval;
use Exception;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\AbandonedCart\Model\ResourceModel\AbandonedCarts;
use Mageplaza\AbandonedCart\Model\ResourceModel\ProductReport;
use Psr\Log\LoggerInterface;

/**
 * Class Indexer
 * @package Mageplaza\AbandonedCart\Cron
 */
class Indexer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AbandonedCarts
     */
    private $abandonedCartResource;

    /**
     * @var TimezoneInterface
     */
    private $_localeDate;

    /**
     * @var bool
     */
    private $_isReindex;

    /**
     * @var ProductReport
     */
    private $productReportResource;

    /**
     * Indexer constructor.
     *
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param AbandonedCarts $abandonedCartResource
     * @param ProductReport $productReportResource
     */
    public function __construct(
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        AbandonedCarts $abandonedCartResource,
        ProductReport $productReportResource
    ) {
        $this->logger                = $logger;
        $this->_localeDate           = $localeDate;
        $this->abandonedCartResource = $abandonedCartResource;
        $this->productReportResource = $productReportResource;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            if ($this->_isReindex) {
                $date = null;
            } else {
                $currentDate = $this->_localeDate->date();
                $date        = $currentDate->sub(new DateInterval('PT25H'));
            }
            $this->abandonedCartResource->aggregate($date);
            $this->productReportResource->aggregate($date);
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param $isReindex
     *
     * @return $this
     */
    public function setIsReindex($isReindex)
    {
        $this->_isReindex = $isReindex;

        return $this;
    }
}
