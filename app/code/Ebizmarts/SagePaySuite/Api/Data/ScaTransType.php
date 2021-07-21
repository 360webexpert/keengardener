<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

interface ScaTransType
{
    /**
     * Values derived from the 8583 ISO Standard.
     */

    const GOOD_SERVICE_PURCHASE = "GoodsAndServicePurchase";
    const CHECK_ACCEPTANCE = "CheckAcceptance";
    const ACCOUNT_FUNDING = "AccountFunding";
    const QUASI_CASH_TRANSACTION = "QuasiCashTransaction";
    const PREPAID_ACTIVATION_AND_LOAD = "PrepaidActivationAndLoad";
}
