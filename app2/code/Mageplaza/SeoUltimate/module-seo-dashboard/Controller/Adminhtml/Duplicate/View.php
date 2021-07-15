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

namespace Mageplaza\SeoDashboard\Controller\Adminhtml\Duplicate;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\SeoDashboard\Controller\Adminhtml\Report;

/**
 * Class View
 * @package Mageplaza\SeoDashboard\Controller\Adminhtml\Duplicate
 */
class View extends Report
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
     * @type Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * View constructor.
     *
     * @param Action\Context $context
     * @param PageFactory $pageFactory
     * @param ForwardFactory $forwardFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory,
        ForwardFactory $forwardFactory,
        Registry $coreRegistry
    ) {
        $this->_coreRegistry         = $coreRegistry;
        $this->_resultPageFactory    = $pageFactory;
        $this->_resultForwardFactory = $forwardFactory;

        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return Page
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->_resultForwardFactory->create();
            $resultForward->forward('grid');

            return $resultForward;
        }
        $id = $this->getRequest()->getParam('issue_id');
        $this->_coreRegistry->register('issue_id', $id);

        $resultPage = $this->_resultPageFactory->create();

        return $resultPage;
    }
}
