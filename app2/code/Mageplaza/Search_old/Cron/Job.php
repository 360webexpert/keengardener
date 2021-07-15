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
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Search\Cron;

use Mageplaza\Search\Helper\Data;
use Mageplaza\Search\Model\Config\Source\Reindex;

/**
 * Class Job
 * @package Mageplaza\Search\Cron
 */
class Job
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Job constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * execute cron job
     */
    public function execute()
    {
        /** Reindex all data*/
        $reindexConfig = $this->helperData->getConfigGeneral('reindex_search');
        if ($reindexConfig == Reindex::TYPE_CRON_JOB) {
            $this->helperData->createJsonFile();
        }

        /** Reindex New Product, Most Viewed Product and Bestsellers data*/
        if ($reindexConfig != Reindex::TYPE_CRON_JOB) {
            $this->helperData->createAdditionJsonFile();
        }

        return $this;
    }
}
