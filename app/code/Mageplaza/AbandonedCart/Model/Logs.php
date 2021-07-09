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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Model;

use Exception;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote;

/**
 * Class Logs
 * @package Mageplaza\AbandonedCart\Model
 * @method getSubject()
 * @method getEmailContent()
 * @method getSender()
 * @method getCustomerEmail()
 * @method getCustomerName()
 * @method setUpdatedAt(string $date)
 * @method setStatus(bool $true)
 */
class Logs extends AbstractModel
{
    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var ResourceModel\LogsFactory
     */
    private $logsFactory;

    /**
     * Logs constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param TemplateFactory $templateFactory
     * @param Quote $quoteResource
     * @param QuoteFactory $quoteFactory
     * @param ResourceModel\LogsFactory $logsFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        TemplateFactory $templateFactory,
        ResourceModel\LogsFactory $logsFactory
    ) {
        $this->templateFactory = $templateFactory;
        $this->logsFactory     = $logsFactory;

        parent::__construct($context, $registry);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init('Mageplaza\AbandonedCart\Model\ResourceModel\Logs');
    }

    /**
     * @param $quote
     * @param $customerEmail
     * @param $customerName
     * @param $sender
     * @param $subject
     * @param $body
     * @param bool $status
     * @param null $couponCode
     */
    public function saveLogs(
        $quote,
        $customerEmail,
        $customerName,
        $sender,
        $subject,
        $body,
        $status = false,
        $couponCode = null
    ) {
        $this->setSubject($subject)
            ->setCustomerEmail($customerEmail)
            ->setCouponCode($couponCode)
            ->setQuoteId($quote->getId())
            ->setSender($sender)
            ->setCustomerName($customerName)
            ->setSequentNumber(1)
            ->setEmailContent(htmlspecialchars($body))
            ->setStatus($status)
            ->save();
    }

    /**
     * @param $quoteId
     *
     * @return $this
     */
    public function updateRecovery($quoteId)
    {
        try {
            if (!$this->_resource) {
                $this->_resource = $this->logsFactory->create();
                $this->_resource->updateRecovery($quoteId);
            }
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $this;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function clear()
    {
        if (!$this->_resource) {
            $this->_resource = $this->logsFactory->create();
        }

        try {
            $this->_resource->clear();
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
    }
}
