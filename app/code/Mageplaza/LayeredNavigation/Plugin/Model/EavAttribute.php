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

namespace Mageplaza\LayeredNavigation\Plugin\Model;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Mageplaza\LayeredNavigation\Helper\Data;

/**
 * Class EavAttribute
 * @package Mageplaza\LayeredNavigation\Plugin\Model
 */
class EavAttribute
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * EavAttribute constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param Attribute $attribute
     */
    public function beforeSave(Attribute $attribute)
    {
        if ($this->helperData->isEnabled() && $attribute->getAttributeCode() === 'category_ids') {
            $initialAdditionalData = [];
            $additionalData        = (string)$attribute->getData('additional_data');
            if (!empty($additionalData)) {
                $additionalData = $this->helperData->unserialize($additionalData);
                if (is_array($additionalData)) {
                    $initialAdditionalData = $additionalData;
                }
            }
            $dataToAdd = [];
            foreach ($this->helperData->getCategoryTreeFields() as $key) {
                $dataValue = $attribute->getData($key);
                if ($dataValue !== null) {
                    $dataToAdd[$key] = $dataValue;
                }
            }
            $additionalData = array_merge($initialAdditionalData, $dataToAdd);
            $attribute->setData('additional_data', $this->helperData->serialize($additionalData));
        }
    }
}
