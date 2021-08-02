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

namespace Mageplaza\FreeGifts\Plugin\Shipping;

use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Sales\Block\Items\AbstractItems;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Plugin\AbstractPlugin;

/**
 * Class MultiShippingNotice
 * @package Mageplaza\FreeGifts\Plugin
 */
class MultiShippingNotice extends AbstractPlugin
{
    /**
     * @var Http
     */
    protected $_request;

    /**
     * MultiShippingNotice constructor.
     *
     * @param HelperRule $helperRule
     * @param Http $request
     */
    public function __construct(
        HelperRule $helperRule,
        Http $request
    ) {
        $this->_request = $request;
        parent::__construct($helperRule);
    }

    /**
     * @param AbstractItems $subject
     * @param $result
     * @param DataObject $item
     *
     * @return mixed
     * @SuppressWarnings("Unused")
     */
    public function afterGetItemHtml(AbstractItems $subject, $result, DataObject $item)
    {
        if ($this->_request->getFullActionName() !== 'multishipping_checkout_addresses' || !$this->isEnabled()) {
            return $result;
        }

        if ($ruleId = $item->getDataByKey(HelperRule::QUOTE_RULE_ID)) {
            $notice = $this->_helperRule->getRuleById($ruleId)->getNoticeContent();

            return $result . '<div>' . $notice . '</div>';
        }

        return $result;
    }
}
