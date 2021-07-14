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
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Controller\Adminhtml\Term;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\SeoCrosslinks\Controller\Adminhtml\Term;
use Mageplaza\SeoCrosslinks\Model\TermFactory;

/**
 * Class Edit
 * @package Mageplaza\SeoCrosslinks\Controller\Adminhtml\Term
 */
class Edit extends Term
{
    /**
     * Backend session
     *
     * @var Session
     */
    protected $_backendSession;

    /**
     * Page factory
     *
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     *
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * constructor
     *
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param TermFactory $termFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        TermFactory $termFactory,
        Registry $registry,
        Context $context
    ) {
        $this->_backendSession    = $context->getSession();
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;

        parent::__construct($termFactory, $registry, $context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|Redirect|Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('term_id');
        /** @var \Mageplaza\SeoCrosslinks\Model\Term $term */
        $term = $this->_initTerm();
        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_SeoCrosslinks::term');
        $resultPage->getConfig()->getTitle()->set(__('Terms'));
        if ($id) {
            $term->load($id);
            if (!$term->getId()) {
                $this->messageManager->addError(__('This Term no longer exists.'));
                $resultRedirect = $this->_resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'seo/*/edit',
                    [
                        'term_id'  => $term->getId(),
                        '_current' => true
                    ]
                );

                return $resultRedirect;
            }
        }
        $title = $term->getId() ? __('Edit term: #' . $term->getId()) : __('New Term');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->_backendSession->getData('mageplaza_seocrosslinks_term_data', true);
        if (!empty($data)) {
            $term->setData($data);
        }

        return $resultPage;
    }
}
