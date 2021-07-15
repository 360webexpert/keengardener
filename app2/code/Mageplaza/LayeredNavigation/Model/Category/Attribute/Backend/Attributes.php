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

namespace Mageplaza\LayeredNavigation\Model\Category\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;

/**
 * Class Attributes
 * @package Mageplaza\LayeredNavigation\Model\Category\Attribute\Backend
 */
class Attributes extends AbstractBackend
{
    /**
     * Before Attribute Save Process
     *
     * @param DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode === 'mp_ln_hide_attribute_ids') {
            $data = $object->getData($attributeCode);
            if (!is_array($data) || empty($data)) {
                $data = [];
            }
            $object->setData($attributeCode, implode(',', $data));
        }
        if (!$object->hasData($attributeCode)) {
            $object->setData($attributeCode);
        }
        return $this;
    }

    /**
     * After Load Attribute Process
     *
     * @param DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode === 'mp_ln_hide_attribute_ids') {
            $data = $object->getData($attributeCode);
            if ($data) {
                if (is_array($data)) {
                    $object->setData($attributeCode, $data);
                } else {
                    $object->setData($attributeCode, explode(',', $data));
                }
            }
        }
        return $this;
    }
}
