<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Import\Rate\Source\Behavior;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Source\Import\AbstractBehavior;

class RateBasic extends AbstractBehavior
{
    /**
     * @return array
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_ADD_UPDATE => __('Add'),
            Import::BEHAVIOR_CUSTOM => __('Replace'),
            Import::BEHAVIOR_DELETE => __('Delete')
        ];
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'amastratebasic';
    }

    /**
     * @param string $entityCode
     * @return array
     */
    public function getNotes($entityCode): array
    {
        $messages = ['amstrates_rate_import' => [
            Import::BEHAVIOR_ADD_UPDATE => __(
                "This option: <ul>"
                . "<li>keeps rates which are present in both selected shipping method and the file you provided</li>"
                . "<li>keeps rates which are present in selected shipping method"
                . " but is missing from the file you provided</li>"
                . "<li>adds rates which are not available in selected shipping method"
                . " but are present in the file you provided</li></ul>"
            ),
            Import::BEHAVIOR_CUSTOM => __(
                "This option replaces ALL rates in selected shipping method with rates you provided."
            ),
            Import::BEHAVIOR_DELETE => __(
                "This option removes rates you provided from selected shipping method."
            ),
        ]];

        return $messages[$entityCode] ?? [];
    }
}
