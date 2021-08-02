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

namespace Mageplaza\FreeGifts\Block\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Source\Apply;

/**
 * Class ItemRule
 * @package Mageplaza\FreeGifts\Block\Cart
 */
class ItemRule extends AdditionalInfo
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_FreeGifts::cart/item_rule.phtml';

    /**
     * @return array|bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getValidatedItemRules()
    {
        if ($this->getItem()->getDataByKey(HelperRule::QUOTE_RULE_ID) !== null) {
            return false;
        }

        $product = $this->_helperRule->getHelperGift()->getProductById($this->getItem()->getData('product_id'));
        $cachedRules = $this->_registry->registry('mpfreegifts_item_rules');
        $itemRules = (is_array($cachedRules) && isset($cachedRules[$this->getItemId()]))
            ? $cachedRules[$this->getItemId()]
            : $this->_helperRule->setApply(Apply::ITEM)->setProduct($product)->getValidatedRules();
        if (count($itemRules)) {
            return array_values($itemRules);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function showAddGiftButton()
    {
        return $this->isEnabled() && $this->getHelperData()->getCartItem();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getJsonScript()
    {
        return HelperData::jsonEncode(
            $this->_helperRule->prepareJsonScript($this->getItemId(), $this->getValidatedItemRules())
        );
    }
}
