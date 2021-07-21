<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

interface ResultInterface
{
    const SUCCESS       = 'success';
    const ERROR_MESSAGE = 'error_message';
    const RESPONSE      = 'response';

    /**
     * @return bool
     */
    public function getSuccess();

    /**
     * @return void
     */
    public function setSuccess($text);

    /**
     * @return string
     */
    public function getResponse();

    /**
     * @return void
     */
    public function setResponse($text);

    /**
     * @return string
     */
    public function getErrorMessage();

    /**
     * @return void
     */
    public function setErrorMessage($text);
}
