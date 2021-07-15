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

namespace Mageplaza\LayeredNavigation\Model\Layer\Filter;

/**
 * Class CategoryItem
 * @package Mageplaza\LayeredNavigation\Model\Layer\Filter
 */
class CategoryItem extends \Magento\Framework\DataObject implements \Countable
{
    /**
     * @var array
     */
    protected $_items = [];

    /**
     * @var int
     */
    protected $_count = 0;

    protected $startPath = '';

    /**
     * @param null $id
     *
     * @return array|mixed
     */
    public function getItems($id = null)
    {
        if (!$id) {
            $id = $this->startPath;
        }

        return isset($this->_items[$id]) ? $this->_items[$id] : [];
    }

    /**
     * @return array
     */
    public function getAllItems()
    {
        $allItems = [];
        foreach ($this->_items as $items) {
            // @codingStandardsIgnoreLine
            $allItems = array_merge($allItems, $items);
        }

        return $allItems;
    }

    /**
     * @param $parentId
     * @param $item
     *
     * @return $this
     */
    public function addItem($parentId, $item)
    {
        $this->_items[$parentId][] = $item;

        return $this;
    }

    /**
     * @param $startPath
     *
     * @return $this
     */
    public function setStartPath($startPath)
    {
        $this->startPath = $startPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartPath()
    {
        return $this->startPath;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * @param $count
     *
     * @return $this
     */
    public function setCount($count)
    {
        $this->_count = $count;

        return $this;
    }

    /**
     * @param $parentId
     *
     * @return bool
     */
    public function hasChildrenData($parentId)
    {
        return isset($this->_items[$parentId]) && count($this->_items[$parentId]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_items);
    }
}
