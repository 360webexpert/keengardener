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

namespace Mageplaza\FreeGifts\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\FreeGifts\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

/**
 * Class AbstractMassAction
 * @package Mageplaza\FreeGifts\Controller\Adminhtml\Rule
 */
abstract class AbstractMassAction extends Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var RuleCollectionFactory
     */
    protected $_ruleCollectionFactory;

    /**
     * AbstractMassAction constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param RuleCollectionFactory $ruleCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        RuleCollectionFactory $ruleCollectionFactory
    ) {
        $this->_filter = $filter;
        $this->_ruleCollectionFactory = $ruleCollectionFactory;

        parent::__construct($context);
    }

    /**
     * @return AbstractDb
     * @throws LocalizedException
     */
    public function getRuleCollection()
    {
        return $this->_filter->getCollection($this->_ruleCollectionFactory->create());
    }

    /**
     * @param string $path
     *
     * @return ResultRedirect
     */
    protected function getResultRedirect($path)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath($path);
    }
}
