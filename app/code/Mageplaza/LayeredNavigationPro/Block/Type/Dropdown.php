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

namespace Mageplaza\LayeredNavigationPro\Block\Type;

/**
 * Class Dropdown
 * @package Mageplaza\LayeredNavigationPro\Block\Type
 */
class Dropdown extends AbstractType
{
    /** @var string Path to template file. */
    protected $_template = 'Mageplaza_LayeredNavigationPro::type/dropdown.phtml';

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptions()
    {
        $filterModel = $this->getFilterModel();
        $filter = $this->getFilter();

        $options = [
            [
                'value'    => '',
                'label'    => '',
                'selected' => false,
                'disabled' => false
            ]
        ];
        $isShowZero = $filterModel->isShowZero($filter);
        $isShowCounter = $filterModel->isShowCounter($filter);

        foreach ($this->getItems() as $filterItem) {
            if ($filterItem->getCount() == 0 && !$isShowZero) {
                continue;
            }

            $label = $filterItem->getLabel();
            if ($isShowCounter) {
                $label .= ' (' . $filterItem->getCount() . ')';
            }

            $options[] = [
                'value'    => $filterModel->getItemUrl($filterItem),
                'label'    => $label,
                'selected' => $filterModel->isSelected($filterItem),
                'disabled' => $filterItem->getCount() == 0
            ];
        }

        return $options;
    }
}
