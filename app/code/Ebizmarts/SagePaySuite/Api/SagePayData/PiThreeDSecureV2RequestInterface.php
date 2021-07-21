<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface PiThreeDSecureV2RequestInterface
{
    const CRES = 'cRes';

    /**
     * A Base64 encoded, encrypted message sent back by Issuing Bank to your Terminal URL at the end of the 3D-Authentication process.
     * You will receive this value back from the Issuing Bank in a field called cres (lower case cr), but should be passed to Sage Pay as cRes.
     * @return string
     */
    public function getCres();

    /**
     * @param string $message
     * @return void
     */
    public function setCres($message);
}
