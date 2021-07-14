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

namespace Mageplaza\LayeredNavigationPro\Observer;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AssignBackendTypeToSliderAttributeObserver
 * @package Mageplaza\LayeredNavigationPro\Observer
 */
class AssignBackendTypeToSliderAttributeObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var $object AbstractAttribute */
        $object = $observer->getEvent()->getAttribute();
        if (($object->getFrontendInput() == 'text') && in_array(
            $object->getFrontendClass(),
            ['validate-number', 'validate-digits']
        )) {
            $object->setBackendType('decimal');
        }

        return $this;
    }
}
