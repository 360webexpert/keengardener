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

/**
 * Class CreditMemo
 * @package Mageplaza\FreeGifts\Plugin\SaleNotice
 */
class CreditMemo extends SaleNotice
{
    /**
     * @param CreditMemoItem $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetDescription(CreditMemoItem $subject, $result)
    {
        return $this->checkRuleNotice($subject, $result);
    }
}
