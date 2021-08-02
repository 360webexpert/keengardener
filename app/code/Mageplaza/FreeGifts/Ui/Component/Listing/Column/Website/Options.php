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

namespace Mageplaza\FreeGifts\Ui\Component\Listing\Column\Website;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

/**
 * Class Options
 * @package Mageplaza\FreeGifts\Ui\Component\Listing\Column\Website
 */
class Options implements OptionSourceInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Options constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_generateWebsiteOptions();
    }

    /**
     * @return array
     */
    protected function _generateWebsiteOptions()
    {
        $options = [];
        $websiteCollection = $this->_storeManager->getWebsites();
        if (count($websiteCollection)) {
            foreach ($websiteCollection as $website) {
                /** @var Website $website */
                $options[] = [
                    'label' => $website->getName(),
                    'value' => $website->getId(),
                ];
            }
        }

        return $options;
    }
}
