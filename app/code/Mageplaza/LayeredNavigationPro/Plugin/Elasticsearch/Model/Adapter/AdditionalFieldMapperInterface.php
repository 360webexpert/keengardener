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

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter;

/**
 * Interface AdditionalFieldMapperInterface
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter
 */
interface AdditionalFieldMapperInterface
{
    /**
     * @return array
     */
    public function getAdditionalAttributeTypes();

    /**
     * @param array $context
     * @return string
     */
    public function getFiledName($context);
}
