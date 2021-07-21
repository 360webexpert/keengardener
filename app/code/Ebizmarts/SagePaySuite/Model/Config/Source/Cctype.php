<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Config\Source;

/**
 * Class Cctype
 * @package Ebizmarts\SagePaySuite\Model\Config\Source
 */
class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'MI', 'AE', 'DN', 'JCB'];
    }
}
