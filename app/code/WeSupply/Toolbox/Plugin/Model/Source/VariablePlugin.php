<?php
namespace WeSupply\Toolbox\Plugin\Model\Source;

use Magento\Variable\Model\Source\Variables;

class VariablePlugin
{
    /**
     * @param Variables $subject
     * @param $result
     * @return array
     */
    public function afterGetData(Variables $subject, $result)
    {
        $result[] = [
            'value' => 'wesupply_api/integration/wesupply_subdomain',
            'label' => __('Configuration / SubDomain'),
            'group_label' => 'WeSupply'
        ];

        return $result;
    }
}