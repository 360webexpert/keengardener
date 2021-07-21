<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Form;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;

class Failure extends \Magento\Backend\App\AbstractAction
{
    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Form
     */
    private $formModel;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Logger $suiteLogger
     * @param \Ebizmarts\SagePaySuite\Model\Form $formModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ebizmarts\SagePaySuite\Model\Logger\Logger $suiteLogger,
        \Ebizmarts\SagePaySuite\Model\Form $formModel
    ) {
    
        parent::__construct($context);
        $this->suiteLogger = $suiteLogger;
        $this->formModel   = $formModel;
    }

    /**
     * @throws Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            //decode response
            $response = $this->formModel->decodeSagePayResponse($this->getRequest()->getParam("crypt"));
            if (!isset($response["Status"]) || !isset($response["StatusDetail"])) {
                throw new \Magento\Framework\Exception\LocalizedException('Invalid response from Opayo');
            }

            //log response
            $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $response, [__METHOD__, __LINE__]);

            $statusDetail = $response["StatusDetail"];
            $statusDetail = explode(" : ", $statusDetail);
            $statusDetail = $statusDetail[1];

            $this->messageManager->addError($response["Status"] . ": " . $statusDetail);
            $this->_redirect('sales/order_create/index');

            return;
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);
        }
    }
}
