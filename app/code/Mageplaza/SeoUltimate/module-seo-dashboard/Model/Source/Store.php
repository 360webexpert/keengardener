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
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Model\Source;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Store
 * @package Mageplaza\SeoDashboard\Model\Source
 */
class Store
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Store constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * Get store collection
     * @return StoreInterface[]
     */
    public function getStoreCollection()
    {
        return $this->_storeManager->getStores();
    }

    /**
     * To option array()
     * @return array
     */
    public function toOptionArray()
    {
        $option = [
            'label' => __('-- Please Select --'),
            'value' => ''
        ];
        foreach ($this->getStoreCollection() as $item) {
            $option[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }

        return $option;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $arr = [];
        foreach ($this->getStoreCollection() as $item) {
            $arr[] = $item->getName();
        }

        return $arr;
    }
}
