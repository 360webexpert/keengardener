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

namespace Mageplaza\FreeGifts\Plugin;

use Magento\Checkout\CustomerData\AbstractItem;
use Magento\Quote\Model\Quote\ItemFactory;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class AfterDefaultItem
 * @package Mageplaza\FreeGifts\Plugin
 */
class AfterDefaultItem extends AbstractPlugin
{
    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * AfterDefaultItem constructor.
     *
     * @param HelperRule $helperRule
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        HelperRule $helperRule,
        ItemFactory $itemFactory
    ) {
        $this->_itemFactory = $itemFactory;
        parent::__construct($helperRule);
    }

    /**
     * @param AbstractItem $subject
     * @param $result
     *
     * @return mixed
     * @SuppressWarnings("Unused")
     */
    public function afterGetItemData(AbstractItem $subject, $result)
    {
        if (!$this->isEnabled()) {
            return $result;
        }

        $itemFactory = $this->_itemFactory->create();
        $ruleId = $itemFactory->load($result['item_id'])->getDataByKey(HelperRule::QUOTE_RULE_ID);
        $result['mpfreegifts_notice'] = false;
        $result['mpfreegifts_icon'] = false;
        $result['mpfreegifts_ruleId'] = false;

        if ($ruleId !== null) {
            $result['is_visible_in_site_visibility'] = false;
            $result['mpfreegifts_ruleId'] = $ruleId;
            $rule = $this->_helperRule->getRuleById($ruleId);
            $result['mpfreegifts_icon'] = $this->_helperRule->getHelperData()->getGiftIcon();
            if ($rule->isAllowNotice()) {
                $result['mpfreegifts_notice'] = $rule->getNoticeContent();
            }
        }

        return $result;
    }
}
