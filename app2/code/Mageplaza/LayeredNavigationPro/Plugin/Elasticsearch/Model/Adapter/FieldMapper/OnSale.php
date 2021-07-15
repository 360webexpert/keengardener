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
 * @package     LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\FieldMapper;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\AdditionalFieldMapperInterface;

/**
 * Class OnSale
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\FieldMapper
 */
class OnSale implements AdditionalFieldMapperInterface
{
    const ATTRIBUTE_NAME = 'mp_on_sale';
    const ATTRIBUTE_TYPE = 'integer';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionFactory
     */
    protected $customerGroupCollectionFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * OnSale constructor.
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $customerGroupCollectionFactory
     */
    public function __construct(
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CollectionFactory $customerGroupCollectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
    }

    /**
     * @param array $context
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFiledName($context)
    {
        $customerGroupId = !empty($context['customerGroupId'])
            ? $context['customerGroupId']
            : $this->customerSession->getCustomerGroupId();
        $websiteId = !empty($context['websiteId'])
            ? $context['websiteId']
            : $this->storeManager->getStore()->getWebsiteId();

        return self::ATTRIBUTE_NAME . '_' . $customerGroupId . '_' . $websiteId;
    }

    /**
     * @return array
     */
    public function getAdditionalAttributeTypes()
    {
        $groupCollection = $this->customerGroupCollectionFactory->create();
        $websites = $this->storeManager->getWebsites();
        $attributeTypes = [];
        foreach ($groupCollection as $group) {
            foreach ($websites as $website) {
                $attributeTypes[self::ATTRIBUTE_NAME . '_' . $group->getId() . '_' . $website->getId()] =
                    ['type' => self::ATTRIBUTE_TYPE];
            }
        }
        return $attributeTypes;
    }
}
