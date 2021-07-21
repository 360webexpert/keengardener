<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Reports\Tokens;

/**
 * Sage Pay token list
 */
class Index extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Ebizmarts_SagePaySuite::token_report_view';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\Reporting
     */
    private $reportingApi;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Ebizmarts\SagePaySuite\Model\Api\Reporting $reportingApi
    ) {
    
        parent::__construct($context);

        $this->logger       = $logger;
        $this->reportingApi = $reportingApi;
    }

    public function execute()
    {
        $this->_initAction();

        try {
            //check token count in sagepay
            $tokenCount = $this->reportingApi->getTokenCount();
            $tokenCount = (string)$tokenCount->totalnumber;

            $this->messageManager->addWarning(__('Registered tokens in Opayo: %1', $tokenCount));
        } catch (\Ebizmarts\SagePaySuite\Model\Api\ApiException $apiException) {
            $this->logger->critical($apiException);
            $this->messageManager->addError(
                __("Unable to check registered tokens in Opayo: %1", $apiException->getUserMessage())
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addError(__('Unable to check registered tokens in Opayo: %1', $e->getMessage()));
        }

        $this->_view->renderLayout();
    }

    /**
     * Initialize titles, navigation
     *
     * @return $this
     */
    // @codingStandardsIgnoreStart
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Ebizmarts_SagePaySuite::report_sagepaysuite_token_report'
        )->_addBreadcrumb(
            __('Reports'),
            __('Reports')
        )->_addBreadcrumb(
            __('Opayo'),
            __('Opayo')
        )->_addBreadcrumb(
            __('Credit Card Tokens'),
            __('Credit Card Tokens')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Opayo Credit Card Tokens'));
        return $this;
    }
    // @codingStandardsIgnoreEnd
}
