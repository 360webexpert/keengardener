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
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Session;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\FreeGifts\Model\Rule as RuleModel;
use Mageplaza\FreeGifts\Model\RuleFactory;
use Mageplaza\FreeGifts\Model\Source\Apply as ApplyType;

/**
 * Class Rule
 * @package Mageplaza\FreeGifts\Controller
 */
abstract class Rule extends Action
{
    const ADMIN_RESOURCE = 'Mageplaza_FreeGifts::rule';

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var DateTime
     */
    protected $_datetime;

    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var PageFactory
     */
    public $_resultPageFactory;

    /**
     * @var ApplyType
     */
    public $_applyType;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Registry $registry
     * @param DateTime $datetime
     * @param RuleFactory $ruleFactory
     * @param ApplyType $applyType
     * @param Session $catalogSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Registry $registry,
        DateTime $datetime,
        RuleFactory $ruleFactory,
        ApplyType $applyType,
        Session $catalogSession,
        PageFactory $resultPageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_registry = $registry;
        $this->_datetime = $datetime;
        $this->_ruleFactory = $ruleFactory;
        $this->_applyType = $applyType;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_catalogSession = $catalogSession;

        parent::__construct($context);
    }

    /**
     * @return RuleModel
     */
    protected function _initObject()
    {
        $id = (int)$this->getRequest()->getParam('rule_id');
        $apply = $this->getRequest()->getParam('apply');

        /** @var RuleModel $rule */
        $rule = $this->_ruleFactory->create();
        $applyTypes = array_keys($this->_applyType->getOptionHash());

        if (in_array($apply, $applyTypes, true)) {
            $rule->setApplyFor($apply);
        }

        if ($id) {
            $rule->load($id);
        }

        if (!$this->_registry->registry('current_rule')) {
            $this->_registry->register('current_rule', $rule);
        }

        return $rule;
    }

    /**
     * @return ResultPage
     */
    protected function getResultPage()
    {
        return $this->_resultPageFactory->create();
    }

    /**
     * @param string $path
     * @param array $params
     *
     * @return ResultRedirect
     */
    protected function getResultRedirect($path, array $params = [])
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath($path, $params);
    }
}
