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
 * @package     Mageplaza_
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter;

/**
 * Class AdditionalFieldMapper
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter
 */
class AdditionalFieldMapper
{
    const ES_DATA_TYPE_STRING = 'string';
    const ES_DATA_TYPE_TEXT   = 'text';
    const ES_DATA_TYPE_FLOAT  = 'float';
    const ES_DATA_TYPE_INT    = 'integer';
    const ES_DATA_TYPE_DATE   = 'date';

    /** @deprecated */
    const ES_DATA_TYPE_ARRAY = 'array';
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var array
     */
    protected $fields = [];

    /**
     * AdditionalFieldMapper constructor.
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $fields
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $fields = []
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->fields = $fields;
    }

    /**
     * @param $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetAllAttributesTypes($subject, array $result)
    {
        foreach ($this->fields as $fieldName => $fieldType) {
            if (is_object($fieldType) && ($fieldType instanceof AdditionalFieldMapperInterface)) {
                $attributeTypes = $fieldType->getAdditionalAttributeTypes();
                // @codingStandardsIgnoreLine
                $result = array_merge($result, $attributeTypes);
                continue;
            }

            if (empty($fieldName)) {
                continue;
            }
            if ($this->isValidFieldType($fieldType)) {
                $result[$fieldName] = ['type' => $fieldType];
            }
        }

        return $result;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param $attributeCode
     * @param array $context
     *
     * @return string
     */
    public function aroundGetFieldName($subject, callable $proceed, $attributeCode, $context = [])
    {
        if (isset($this->fields[$attributeCode]) && is_object($this->fields[$attributeCode])) {
            $filedMapper = $this->fields[$attributeCode];
            if ($filedMapper instanceof AdditionalFieldMapperInterface) {
                return $filedMapper->getFiledName($context);
            }
        }
        return $proceed($attributeCode, $context);
    }

    /**
     * @param $fieldType
     * @return bool
     */
    protected function isValidFieldType($fieldType)
    {
        switch ($fieldType) {
            case self::ES_DATA_TYPE_STRING:
            case self::ES_DATA_TYPE_DATE:
            case self::ES_DATA_TYPE_INT:
            case self::ES_DATA_TYPE_FLOAT:
                break;
            default:
                $fieldType = false;
                break;
        }
        return $fieldType;
    }
}
