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

namespace Mageplaza\SeoDashboard\Controller\Adminhtml\Dashboard;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\SeoDashboard\Controller\Adminhtml\Report as ReportController;
use Mageplaza\SeoDashboard\Helper\Report;

/**
 * Class LoadData
 * @package Mageplaza\SeoDashboard\Controller\Adminhtml\Dashboard
 */
class LoadData extends ReportController
{
    /**
     * @type Report
     */
    protected $_report;

    /**
     * LoadData constructor.
     *
     * @param Context $context
     * @param Report $report
     */
    public function __construct(
        Action\Context $context,
        Report $report
    ) {
        parent::__construct($context);

        $this->_report = $report;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $this->_report->mappingFieldsData();
            $success = true;
        } catch (Exception $e) {
            $success = false;
        }

        return $this->getResponse()->representJson(
            Report::jsonEncode(['success' => $success])
        );
    }
}
