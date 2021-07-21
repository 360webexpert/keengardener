<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface PiThreeDSecureRequestInterface
{
    const PAR_ES = 'paRes';

    /**
     * A Base64 encoded, encrypted message with the results of the 3-D Secure authentication.
     * @return string
     */
    public function getParEs();

    /**
     * @param string $message
     * @return void
     */
    public function setParEs($message);
}
