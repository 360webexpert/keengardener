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
 * @package     Mageplaza_SeoUrl
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoUrl\Plugin\Block\Product\View\Type;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as TypeConfigurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\SeoUrl\Helper\Data as HelperData;
use Zend_Serializer_Exception;

/**
 * Class Configurable
 * @package Mageplaza\SeoUrl\Plugin\Block\Product\View\Type
 */
class Configurable
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Configurable constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->_helperData = $helperData;
    }

    /**
     * @param TypeConfigurable $subject
     * @param $result
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws Zend_Serializer_Exception
     * @SuppressWarnings(Unused)
     */
    public function afterGetJsonConfig(TypeConfigurable $subject, $result)
    {
        if (!$this->_helperData->isEnabled()) {
            return $result;
        }

        $config = HelperData::jsonDecode($result);
        $config['seoUrl']['optionCollection'] = $this->_helperData->getOptionsArray();
        $config['seoUrl']['urlSuffix'] = $this->_helperData->getCategoryUrlSuffix();

        return HelperData::jsonEncode($config);
    }
}
