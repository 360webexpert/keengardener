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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Model\Config\Source;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class StaticBlock
 * @package Mageplaza\Shopbybrand\Model\Config\Source
 */
class StaticBlock implements ArrayInterface
{
    /**
     * @type BlockFactory
     */
    protected $_blockFactory;

    /**
     * @param BlockFactory $blockFactory
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->_blockFactory = $blockFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getOptionArray() as $identifier => $title) {
            $options[] = [
                'label' => $title,
                'value' => $identifier
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $resultBlocks = ['' => __('-- Please Select --')];
        $blockCollection = $this->_blockFactory->create()->getCollection();
        foreach ($blockCollection as $block) {
            $resultBlocks[$block->getData('identifier')] = $block->getData('title');
        }

        return $resultBlocks;
    }
}
