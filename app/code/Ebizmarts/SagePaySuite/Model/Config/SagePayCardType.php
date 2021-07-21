<?php

namespace Ebizmarts\SagePaySuite\Model\Config;

/**
 * Class SagePayCardType.
 * Sage Pay to Magento card type code.
 *
 * @package Ebizmarts\SagePaySuite\Model\Config
 */
class SagePayCardType
{
    /** @var array */
    private $ccTypesMap = [
        'Visa'            => 'VI',
        'VisaDebit'       => 'VI',
        'MasterCard'      => 'MC',
        'DebitMasterCard' => 'MC',
        'Maestro'         => 'MI',
        'AmericanExpress' => 'AE',
        'Diners'          => 'DN',
        'JCB'             => 'JCB'
    ];

    public function convert($cardType)
    {
        $magentoCardType = $cardType;

        if (isset($this->ccTypesMap[$cardType])) {
            $magentoCardType = $this->ccTypesMap[$cardType];
        }

        return $magentoCardType;
    }
}
