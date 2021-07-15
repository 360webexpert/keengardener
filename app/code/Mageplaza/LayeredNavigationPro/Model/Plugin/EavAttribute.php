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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Mageplaza\LayeredNavigationPro\Helper\Data;

/**
 * Class EavAttribute
 * @package Mageplaza\LayeredNavigationPro\Model\Plugin
 */
class EavAttribute
{
    /** @var \Mageplaza\LayeredNavigationPro\Helper\Data */
    protected $layerHelper;

    /**
     * EavAttribute constructor.
     *
     * @param \Mageplaza\LayeredNavigationPro\Helper\Data $layerHelper
     */
    public function __construct(Data $layerHelper)
    {
        $this->layerHelper = $layerHelper;
    }

    /**
     * Set base data to Attribute
     *
     * @param Attribute $attribute
     *
     * @throws \Zend_Serializer_Exception
     */
    public function beforeSave(Attribute $attribute)
    {
        if ($this->layerHelper->isEnabled()) {
            $initialAdditionalData = [];
            $additionalData = (string) $attribute->getData('additional_data');
            if (!empty($additionalData)) {
                $additionalData = $this->layerHelper->unserialize($additionalData);
                if (is_array($additionalData)) {
                    $initialAdditionalData = $additionalData;
                }
            }

            $dataToAdd = [];
            foreach ($this->layerHelper->getLayerAdditionalFields() as $key) {
                $dataValue = $attribute->getData($key);
                if (null !== $dataValue) {
                    $dataToAdd[$key] = $dataValue;
                }
            }
            $additionalData = array_merge($initialAdditionalData, $dataToAdd);
            $attribute->setData('additional_data', $this->layerHelper->serialize($additionalData));
        }
    }
}
