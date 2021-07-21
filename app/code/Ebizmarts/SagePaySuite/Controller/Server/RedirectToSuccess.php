<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Server;

use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;

class RedirectToSuccess extends \Magento\Framework\App\Action\Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Success constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger
    ) {

        parent::__construct($context);

        $this->logger          = $logger;
    }

    public function execute()
    {
        $request = $this->getRequest();

        $storeId = $request->getParam("_store");
        $encryptedQuoteId = $request->getParam("quoteid");

        //redirect to success via javascript
        $this->getResponse()->setBody(
            '<script>window.top.location.href = "'
            . $this->_url->getUrl('*/*/success', ['_secure' => true, '_store' => $storeId]). '?quoteid=' .  urlencode($encryptedQuoteId)
            . '";</script>'
        );
    }
}
