<?php
/**
 * Copyright © 2018 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Observer;

use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class SystemConfigEdit implements ObserverInterface
{
    /**
     * @var Data
     */
    private $_suiteHelper;

    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * @var Reporting
     */
    private $_reportingApi;

    /**
     * SystemConfigEdit constructor.
     * @param Data $suiteHelper
     * @param ManagerInterface $messageManager
     * @param Reporting $reportingApi
     */
    public function __construct(
        Data $suiteHelper,
        ManagerInterface $messageManager,
        Reporting $reportingApi
    ) {
        $this->_suiteHelper = $suiteHelper;
        $this->_messageManager = $messageManager;
        $this->_reportingApi = $reportingApi;
    }

    /**
     * Observer payment config section save to validate license and
     * check reporting api credentials.
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $section = $observer->getEvent()->getRequest()->getParam('section');
        if ($section == "payment") {
            if (!$this->isLicenseKeyValid()) {
                $this->_messageManager->addWarning(__('Your Opayo Suite license is invalid.'));
            }

            $this->verifyReportingApiCredentialsByCallingVersion();
        }
    }

    private function verifyReportingApiCredentialsByCallingVersion()
    {
        try {
            $this->_reportingApi->getVersion();
        } catch (ApiException $apiException) {
            $message = $apiException->getUserMessage();
            $message .= ' ';
            $message .= sprintf("<a target='_blank' href='http://wiki.ebizmarts.com/configuration-guide-1'>%s</a>", __('Configuration guide'));
            $this->_messageManager->addWarning($message);
        } catch (\Exception $e) {
            $this->_messageManager->addWarning(__('Can not establish connection with Opayo API.'));
        }
    }

    /**
     * @return bool
     */
    private function isLicenseKeyValid()
    {
        return $this->_suiteHelper->verify();
    }
}
