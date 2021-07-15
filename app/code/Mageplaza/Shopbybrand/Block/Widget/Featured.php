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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Shopbybrand\Helper\Data as Helper;
use Mageplaza\Shopbybrand\Model\BrandFactory;

/**
 * Class Featured
 *
 * @package Mageplaza\Shopbybrand\Block\Brand
 */
class Featured extends AbstractBrand
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
     * Featured constructor.
     *
     * @param Context $context
     * @param BrandFactory $brandFactory
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        BrandFactory $brandFactory,
        Helper $helper
    ) {
        $this->_brandFactory = $brandFactory;

        parent::__construct($context, $helper);
    }

    /**
     * get feature brand
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCollection()
    {
        $featureBrands = [];
        $collection = $this->_brandFactory->create()->getBrandCollection();
        foreach ($collection as $brand) {
            if ($brand->getIsFeatured()) {
                $featureBrands[] = $brand;
            }
        }

        return $featureBrands;
    }
}
