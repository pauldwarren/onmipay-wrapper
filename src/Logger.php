<?php

namespace Tomahawk\OmnipayWrapper;

use Illuminate\Support\Facades\Log;
use Tomahawk\OmnipayWrapper\Contracts\PaymentGatewayInterface;

class Logger
{
    const COLOR_RESET = "\e[0m"; // back to white.
    const COLOR_ERROR = "\e[41m"; // 31 = red; 41 = red background.
    const COLOR_SUCCESS = "\e[32m"; // 32 = green;
    const COLOR_WARNING = "\e[33m"; // 33 = orange;
    const COLOR_INFO = "\e[34m"; // 34 = blue;
    const COLOR_ERROR2 = "\e[35m"; // 35 = pink;
    const COLOR_INFO2 = "\e[36m"; // 36 = teal;
    const COLOR_INFO3 = "\e[40m"; // 30 = black; 40 = pure black background.

    /**
     * @param PaymentGatewayInterface $gateway
     * @param $amount
     * @param $transactionId
     * @param $stage
     *
     * @return null
     */
    public static function log($gateway, $amount, $transactionId, $stage, $message, $response = null)
    {
        if ( ! config('logging.omnipaywrapper.channel')) {
            return null;
        }

        // TODO allow for custom formatting.
        $message = $_SERVER['COMPUTERNAME'] . ' v' . config('app.version') . ' ' .
                   Logger::COLOR_INFO2 . ($gateway->getClientName() ?? 'Unknown') . Logger::COLOR_RESET .
                   ' Order: ' . Logger::COLOR_INFO2 . $transactionId . Logger::COLOR_RESET .
                   ' ' . Logger::COLOR_ERROR2 . '$' . $amount . Logger::COLOR_RESET . ' ' .
                   $message .
                   'ClientUUID:' . Logger::COLOR_INFO . ($gateway->uuid ?? 'NA') . Logger::COLOR_RESET;
//                   " - " .
//                   Logger::COLOR_INFO . "Version: " . $versionInfo . Logger::COLOR_RESET;
        if ($response) {
            $message .= ' ' . print_r($response, true) . '==';
        }

        $gateway->logPayment($gateway, $amount, $transactionId, $stage, $response);

        Log::channel(config('logging.omnipaywrapper.channel'))->info($message);
    }
}