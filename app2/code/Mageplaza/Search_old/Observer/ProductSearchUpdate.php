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
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Search\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Search\Helper\Data;
use Mageplaza\Search\Model\Config\Source\Reindex;

/**
 * Class ProductSearchUpdate
 * @package Mageplaza\Search\Observer
 */
class ProductSearchUpdate implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * ProductSearchUpdate constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $reindexConfig = $this->_helper->getConfigGeneral('reindex_search');
        if ($reindexConfig == Reindex::TYPE_PRODUCT_SAVE) {
            $this->_helper->createJsonFile();
        }

        return $this;
    }
}
