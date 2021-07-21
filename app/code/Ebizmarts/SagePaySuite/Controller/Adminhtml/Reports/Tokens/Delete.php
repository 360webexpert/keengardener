<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Reports\Tokens;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;

class Delete extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Ebizmarts_SagePaySuite::token_report_delete';

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Token
     */
    private $tokenModel;

    private $tokenId;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Logger $suiteLogger
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Ebizmarts\SagePaySuite\Model\Token $tokenModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Logger $suiteLogger,
        \Psr\Log\LoggerInterface $logger,
        \Ebizmarts\SagePaySuite\Model\Token $tokenModel
    ) {
    
        parent::__construct($context);
        $this->suiteLogger = $suiteLogger;
        $this->logger      = $logger;
        $this->tokenModel  = $tokenModel;
    }

    public function execute()
    {
        try {
            $this->_view->loadLayout();
            $this->tokenId = $this->getRequest()->getParam('id');

            if (empty($this->tokenId)) {
                throw new \Magento\Framework\Validator\Exception(__('Unable to delete token: Invalid token id.'));
            }

            $token = $this->tokenModel->loadToken($this->tokenId);

            //delete
            $token->deleteToken();

            $this->messageManager->addSuccess(__('Token deleted successfully.'));
        } catch (\Ebizmarts\SagePaySuite\Model\Api\ApiException $apiException) {
            $this->logger->critical($apiException);
            $this->messageManager->addError(__($apiException->getUserMessage()));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addError(__($e->getMessage()));
        }

        $this->_redirect('*/*/index');
    }
}
