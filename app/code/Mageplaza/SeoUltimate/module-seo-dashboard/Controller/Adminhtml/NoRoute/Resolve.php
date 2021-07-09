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

namespace Mageplaza\SeoDashboard\Controller\Adminhtml\NoRoute;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\SeoDashboard\Controller\Adminhtml\Report;
use Mageplaza\SeoDashboard\Model\NoRouteFactory;

/**
 * Class Resolve
 * @package Mageplaza\SeoDashboard\Controller\Adminhtml\NoRoute
 */
class Resolve extends Report
{
    /**
     * @type PageFactory|null
     */
    protected $_resultPageFactory = null;

    /**
     * @type ForwardFactory|null
     */
    protected $_resultForwardFactory = null;

    /**
     * @type NoRouteFactory
     */
    protected $_noRouteFactory = null;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * Resolve constructor.
     *
     * @param Action\Context $context
     * @param PageFactory $pageFactory
     * @param ForwardFactory $forwardFactory
     * @param NoRouteFactory $noRouteFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        NoRouteFactory $noRouteFactory
    ) {
        $this->_resultPageFactory    = $pageFactory;
        $this->_resultForwardFactory = $forwardFactory;
        $this->_noRouteFactory       = $noRouteFactory;
        $this->_messageManager       = $context->getMessageManager();

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('issue_id');
        try {
            $this->_noRouteFactory->create()->load($id)->delete();
            $this->messageManager->addSuccessMessage(__('Delete issue %1 success', $id));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Resolve issue %1 fails', $id));
        }

        return $this->_redirect('seo/noroute');
    }
}
