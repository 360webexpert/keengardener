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
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Model\Page;

use Exception;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\SeoDashboard\Helper\Report;

/**
 * Class SaveHandler
 * @package Mageplaza\SeoDashboard\Model\Page
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @type Report
     */
    protected $_report;

    /**
     * SaveHandler constructor.
     *
     * @param Report $report
     */
    function __construct(Report $report)
    {
        $this->_report = $report;
    }

    /**
     * @param object $entity
     * @param array $arguments
     *
     * @return bool|object
     * @throws Exception
     * @throws NoSuchEntityException
     */
    public function execute($entity, $arguments = [])
    {
        if ($this->_report->getDbReportConfig('enable')) {
            $this->_report->reloadMediateTable($entity, Report::PAGE_ENTITY);
        }

        return $entity;
    }
}
