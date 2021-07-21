<?php

namespace Ebizmarts\SagePaySuite\Test\Api;

use Magento\TestFramework\Helper\Bootstrap;

class Helper
{
    const TEST_API_KEY = "snEEZ7EFaM5q9GzBspep";
    const TEST_API_PASSWORD = "MrzrB8u3CST4FLLNRXL6";
    const TEST_REPORTING_USERNAME = "functional_tester";
    const TEST_REPORTING_PASSWORD = '47AEt@YEc^gfkEM2D+Ex5$sVcRbdm6gV';

    /** @var \Magento\Config\Model\Config */
    private $config;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\Reporting */
    private $reporting;

    /** @var \Ebizmarts\SagePaySuite\Helper\Data  */
    private $suiteHelper;

    /** @var \Magento\Config\Model\Config\Backend\Encrypted */
    private $configEcrypted;

    public function __construct()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->config = $this->objectManager->create('Magento\Config\Model\Config');
        $this->reporting = $this->objectManager->create('Ebizmarts\SagePaySuite\Model\Api\Reporting');
        $this->suiteHelper = $this->objectManager->create('Ebizmarts\SagePaySuite\Helper\Data');
        $this->configEncrypted = $this->objectManager->create('Magento\Config\Model\Config\Backend\Encrypted');
    }

    public function getTransactionDetails($vpsTxId)
    {
        $this->config->setDataByPath("sagepaysuite/global/mode", \Ebizmarts\SagePaySuite\Model\Config::MODE_DEVELOPMENT);
        $this->config->save();
        $this->saveReportingApiUser();

        $this->saveReportingApiPassword();

        $transactionDetails = $this->reporting->getTransactionDetailsByVpstxid($this->suiteHelper->removeCurlyBraces($vpsTxId));

        return $transactionDetails;
    }

    public function savePiKey()
    {
        $this->config->setDataByPath("payment/sagepaysuitepi/key", self::TEST_API_KEY);
        $this->config->save();
    }

    public function savePiPassword()
    {
        $model = $this->configEncrypted;
        $model->setPath('payment/sagepaysuitepi/password');
        $model->setScopeId(0);
        $model->setScope('default');
        $model->setScopeCode('');
        $model->setValue(self::TEST_API_PASSWORD);
        $model->save();
    }

    public function saveReportingApiUser()
    {
        $this->config->setDataByPath("sagepaysuite/global/reporting_user", self::TEST_REPORTING_USERNAME);
        $this->config->save();
    }

    public function saveReportingApiPassword()
    {
        $model = $this->configEncrypted;
        $model->setPath('sagepaysuite/global/reporting_password');
        $model->setScopeId(0);
        $model->setScope('default');
        $model->setScopeCode('');
        $model->setValue(self::TEST_REPORTING_PASSWORD);
        $model->save();
    }
}
