<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon;

/**
 * Class \Ess\M2ePro\Controller\Adminhtml\Wizard\InstallationAmazon\AfterGetTokenAutomatic
 */
class AfterGetTokenAutomatic extends AfterGetTokenAbstract
{
    //########################################

    protected function getAccountData()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params)) {
            return $this->indexAction();
        }

        $requiredFields = [
            'Merchant',
            'Marketplace',
            'MWSAuthToken',
            'Signature',
            'SignedString'
        ];

        foreach ($requiredFields as $requiredField) {
            if (!isset($params[$requiredField])) {
                $message = $this->__('The Amazon token obtaining is currently unavailable.');
                throw new \Exception($message);
            }
        }

        return array_merge(
            $this->getAmazonAccountDefaultSettings(),
            [
                'title'          => $params['Merchant'],
                'marketplace_id' => $this->getHelper('Data\Session')->getValue('marketplace_id'),
                'merchant_id'    => $params['Merchant'],
                'token'          => $params['MWSAuthToken'],
            ]
        );
    }

    //########################################
}
