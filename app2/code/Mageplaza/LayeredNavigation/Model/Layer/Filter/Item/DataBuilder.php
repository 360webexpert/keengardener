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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Model\Layer\Filter\Item;

/**
 * Class DataBuilder
 * @package Mageplaza\LayeredNavigation\Model\Layer\Filter\Item
 */
class DataBuilder
{
    /**
     * Array of items data
     * array(
     *      $index => array(
     *          'label' => $label,
     *          'value' => $value,
     *          'parent_id' => $parentId,
     *          'position' => $position,
     *          'level' => $level,
     *          'children_data' => $children,
     *          'count' => $count
     *      )
     * )
     *
     * @return array
     */
    protected $_itemsData = [];
    /**
     * @var int
     */
    protected $countItems = 0;

    /**
     * Add Item Data
     *
     * @param string $label
     * @param string $value
     * @param string $parentId
     * @param string $position
     * @param string $level
     * @param int $count
     *
     * @return void
     */
    public function addItemData($label, $value, $parentId, $position, $level, $count)
    {
        $this->countItems++;

        $this->_itemsData[$parentId][] = [
            'label'     => $label,
            'value'     => $value,
            'parent_id' => $parentId,
            'position'  => $position,
            'level'     => $level,
            'count'     => $count,
        ];
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->countItems;
    }

    /**
     * Get Items Data
     *
     * @return array
     */
    public function build()
    {
        $result           = $this->_itemsData;
        $this->_itemsData = [];
        $this->countItems = 0;

        return $result;
    }
}
