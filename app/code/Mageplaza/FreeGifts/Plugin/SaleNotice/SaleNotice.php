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

namespace Mageplaza\FreeGifts\Plugin\SaleNotice;

use Magento\Sales\Model\Order\Creditmemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Shipment\Item as ShipmentItem;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class SaleNotice
 * @package Mageplaza\FreeGifts\Plugin\SaleNotice
 */
abstract class SaleNotice
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var OrderItem
     */
    protected $_orderItem;

    /**
     * Order constructor.
     *
     * @param HelperRule $helperRule
     */
    public function __construct(
        HelperRule $helperRule
    ) {
        $this->_helperRule = $helperRule;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_helperRule->getHelperData()->isEnabled();
    }

    /**
     * @param OrderItem $orderItem
     *
     * @return $this
     */
    public function setOrderItem(OrderItem $orderItem)
    {
        $this->_orderItem = $orderItem;

        return $this;
    }

    /**
     * @return string
     */
    public function getItemNotice()
    {
        $notice = '';
        if ($ruleId = (int)$this->_orderItem->getDataByKey(HelperRule::QUOTE_RULE_ID)) {
            $rule = $this->_helperRule->getRuleById($ruleId);
            $notice = ' ' . $rule->getNoticeContent();
        }

        return $notice;
    }

    /**
     * @param CreditMemoItem|InvoiceItem|ShipmentItem $subject
     * @param $result
     *
     * @return string
     */
    public function checkRuleNotice($subject, $result)
    {
        if (!$this->isEnabled()) {
            return $result;
        }

        $orderItem = $subject->getOrderItem();
        $notice = $this->setOrderItem($orderItem)->getItemNotice();

        if (!empty($result) && strpos($result, $notice) !== false) {
            return $result;
        }

        return $result . $notice;
    }
}
