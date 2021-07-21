<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Ebizmarts\SagePaySuite\Model\Logger;

class Logger extends \Monolog\Logger
{

    /**
     * SagePaySuite log files
     */
    const LOG_REQUEST   = 'Request';
    const LOG_CRON      = 'Cron';
    const LOG_EXCEPTION = 'Exception';

    // @codingStandardsIgnoreStart
    protected static $levels = [
        self::LOG_REQUEST   => 'Request',
        self::LOG_CRON      => 'Cron',
        self::LOG_EXCEPTION => 'Exception'
    ];
    // @codingStandardsIgnoreEnd

    /**
     * @param $logType
     * @param $message
     * @param array $context
     * @return bool
     */
    public function sageLog($logType, $message, $context = [])
    {
        $message = $this->messageForLog($message);
        $message .= "\r\n";

        return $this->addRecord($logType, $message, $context);
    }

    public function logException($exception, $context = [])
    {
        $message = $exception->getMessage();
        $message .= "\n";
        $message .= $exception->getTraceAsString();
        $message .= "\r\n\r\n";

        return $this->addRecord(self::LOG_EXCEPTION, $message, $context);
    }

    /**
     * @param $message
     * @return string
     */
    private function messageForLog($message)
    {
        if ($message === null) {
            $message = "NULL";
        }

        if (is_array($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT);
        }

        if (is_object($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT);
        }

        if (!empty(json_last_error())) {
            $message = json_last_error_msg();
        }

        $message = (string)$message;

        return $message;
    }
}
