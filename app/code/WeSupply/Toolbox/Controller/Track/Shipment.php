<?php

namespace WeSupply\Toolbox\Controller\Track;

use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use WeSupply\Toolbox\Helper\Data as WsHelper;

class Shipment extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var WsHelper
     */
    protected $_helper;

    /**
     * Shipment constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param WsHelper $wsHelper
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        WsHelper $wsHelper
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_helper = $wsHelper;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Shipment Tracking'));

        return $resultPage;
    }
}
