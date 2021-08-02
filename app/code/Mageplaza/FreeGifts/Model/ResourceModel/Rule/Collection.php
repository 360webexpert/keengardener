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

namespace Mageplaza\FreeGifts\Model\ResourceModel\Rule;

use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection;
use Mageplaza\FreeGifts\Model\ResourceModel\Rule as RuleResourceModel;
use Mageplaza\FreeGifts\Model\Rule as RuleModel;

/**
 * Class Collection
 * @package Mageplaza\FreeGifts\Model\ResourceModel\Rule
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(RuleModel::class, RuleResourceModel::class);
    }
}
