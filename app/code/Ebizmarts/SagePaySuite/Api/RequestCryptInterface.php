<?php

namespace Ebizmarts\SagePaySuite\Api;

interface RequestCryptInterface
{
    public function encrypt($dataToEncrypt);

    public function decrypt($dataToDecrypt);
}
