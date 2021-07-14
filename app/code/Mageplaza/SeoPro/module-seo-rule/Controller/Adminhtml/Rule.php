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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\SeoRule\Helper\Data;
use Mageplaza\SeoRule\Model\RuleFactory;

/**
 * Class Rule
 * @package Mageplaza\SeoRule\Controller\Adminhtml
 */
abstract class Rule extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mageplaza_SeoRule::rule';

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Mageplaza\SeoRule\Model\Rule
     */
    protected $seoRuleFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param RuleFactory $seoRuleFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RuleFactory $seoRuleFactory,
        Data $helperData
    ) {
        parent::__construct($context);

        $this->coreRegistry   = $coreRegistry;
        $this->seoRuleFactory = $seoRuleFactory;
        $this->helperData     = $helperData;
    }

    /**
     * Init action
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Mageplaza_SeoRule::rule')->_addBreadcrumb(__('SeoRule'), __('Manage Rules'));

        return $this;
    }
}
