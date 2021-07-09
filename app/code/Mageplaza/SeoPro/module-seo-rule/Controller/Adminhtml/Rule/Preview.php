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

namespace Mageplaza\SeoRule\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Mageplaza\SeoRule\Controller\Adminhtml\Rule;
use Mageplaza\SeoRule\Helper\Data;
use Mageplaza\SeoRule\Model\RuleFactory;

/**
 * Class Preview
 * @package Mageplaza\SeoRule\Controller\Adminhtml\Rule
 */
class Preview extends Rule
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Preview constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param RuleFactory $seoRuleFactory
     * @param Data $helperData
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RuleFactory $seoRuleFactory,
        Data $helperData,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context, $coreRegistry, $seoRuleFactory, $helperData);
    }

    /**
     * @return $this|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        if ($request->getParam('isAjax')) {
            $params = $request->getParams();
            $data   = $this->helperData->preview(
                $params['metaTitle'],
                $params['metaDescription'],
                $params['metaKeywords'],
                $this->_getSession()->getSeoRuleType()
            );

            return $this->resultJsonFactory->create()->setData($data);
        }
    }
}
