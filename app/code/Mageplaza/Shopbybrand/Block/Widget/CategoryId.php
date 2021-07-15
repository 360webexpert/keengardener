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

namespace Mageplaza\Shopbybrand\Block\Widget;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Shopbybrand\Helper\Data as Helper;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Mageplaza\Shopbybrand\Model\CategoryFactory;

/**
 * Class CategoryId
 *
 * @package Mageplaza\Shopbybrand\Block\Brand
 */
class CategoryId extends AbstractBrand
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_Shopbybrand::widget/brandlist.phtml';

    /**
     * @type BrandFactory
     */
    protected $_brandFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * CategoryId constructor.
     *
     * @param Context $context
     * @param BrandFactory $brandFactory
     * @param CategoryFactory $categoryFactory
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        BrandFactory $brandFactory,
        CategoryFactory $categoryFactory,
        Helper $helper
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_brandFactory = $brandFactory;

        parent::__construct($context, $helper);
    }

    /**
     * @return string
     */
    public function getOptionIds()
    {
        $str = $this->getData('category_id');
        $sql = 'main_table.cat_id IN (' . $str . ')';
        $result = [];
        $brands = $this->_categoryFactory->create()->getCategoryCollection($sql, null)->getData();
        foreach ($brands as $brand => $item) {
            $result[] = $item['option_id'];
        }

        return implode(",", array_unique($result));
    }

    /**
     * get brand by option IDs
     *
     * @return Collection
     * @throws LocalizedException
     */
    public function getCollection()
    {
        $collection = $this->_brandFactory->create()->getBrandCollection(
            null,
            ['main_table.option_id' => ['in' => $this->getOptionIds()]]
        );

        return $collection;
    }
}
