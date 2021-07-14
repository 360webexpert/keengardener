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

namespace Mageplaza\Shopbybrand\Block\Brand;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Mageplaza\Shopbybrand\Block\Brand;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class Category
 * @package Mageplaza\Shopbybrand\Block\Brand
 */
class Category extends Brand
{
    /**
     * @var Collection
     */
    protected $brandCategoryCollection;

    /**
     * @param $char
     *
     * @return Collection|mixed
     */
    public function getCollectionByChar($char)
    {
        if (!$this->brandCategoryCollection) {
            $this->brandCategoryCollection = $this->getCollection();
        }

        $collection = clone $this->brandCategoryCollection;
        $sqlString = $this->helper->checkCharacter($char);
        $collection->getSelect()->where($sqlString);

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function getCollection($type = null, $option = null)
    {
        return parent::getCollection(Data::CATEGORY, $this->getOptionIds());
    }
}
