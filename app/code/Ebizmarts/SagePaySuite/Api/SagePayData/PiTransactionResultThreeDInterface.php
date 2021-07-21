<?php
namespace Ebizmarts\SagePaySuite\Api\SagePayData;

/**
 * Interface PiTransactionResultThreeDInterface
 *
 * @package Ebizmarts\SagePaySuite\Api\SagePayData
 */
interface PiTransactionResultThreeDInterface
{
    const STATUS = 'status';

    /**
     * The 3-D Secure status of the transaction, if applied.
     *
     * Authenticated 3-D Secure checks carried out and user authenticated correctly.
     *
     * NotChecked 3-D Secure checks were not performed. This indicates that 3-D Secure was either switched off
     * at the account level, or disabled at transaction registration with the apply3DSecure set to Disable.
     *
     * NotAuthenticated 3-D Secure authentication checked, but the user failed the authentication.
     *
     * Error Authentication could not be attempted due to data errors or service unavailability in one of the parties
     * involved in the check.
     *
     * CardNotEnrolled This means that the card is not in the 3-D Secure scheme.
     *
     * IssuerNotEnrolled This means that the issuer is not part of the 3-D Secure scheme.
     *
     * MalformedOrInvalid This means that there is a problem with creating or receiving the 3D Secure data.
     * These should not occur on the live environment.
     *
     * AttemptOnly This means that the cardholder attempted to authenticate themselves but the process did not complete.
     * A liability shift may occur for non-Maestro cards, depending on your merchant agreement.
     *
     * Incomplete This means that the 3D Secure authentication was not available (normally at the card issuer site).
     *
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return void
     */
    public function setStatus($status);
}
