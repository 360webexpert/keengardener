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

namespace Mageplaza\FreeGifts\Model\Source;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class CustomerGroup
 * @package Mageplaza\FreeGifts\Model\Source
 */
class CustomerGroup implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * CustomerGroup constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_collectionFactory->create()->toOptionArray();
    }
}
