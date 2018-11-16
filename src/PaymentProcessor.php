<?php

namespace Tomahawk\OmnipayWrapper;

use Omnipay\Omnipay;
use Tomahawk\OmnipayWrapper\Contracts\PaymentGatewayInterface;

class PaymentProcessor
{
    const STAGE_ATTEMPT = 'attempt';
    const STAGE_SUCCESS = 'success';
    const STAGE_REDIRECT = 'redirect';
    const STAGE_FAILURE = 'failure';

    const PROVIDER_AUTHORIZENET_API = "AuthorizeNetApi";
    const OMNIPAY_PROVIDER_AUTHORIZENET_API = 'AuthorizeNetApi_Api'; // Keep the two separate incase the class ever changes in laravel.
    const PROVIDER_AUTHORIZENET_HOSTED = "AuthorizeNetHP";
    const OMNIPAY_PROVIDER_AUTHORIZENET_HOSTED = 'AuthorizeNetApi_HostedPage'; // Keep the two separate incase the class ever changes in laravel.

    /**
     * @param PaymentGatewayInterface $gateway
     * @param $amount
     * @param $transactionId
     * @param null $card
     *
     * @return string
     */
    public static function payment($gateway, $amount, $transactionId, $card = null)
    {
        try {
            $provider = self::getProvider($gateway);
        } catch (\Exception $exception) {
            Logger::log($gateway, $amount, $transactionId, self::STAGE_FAILURE, Logger::COLOR_ERROR . " Payment Provider could not be found." . Logger::COLOR_RESET . " ", $exception->getMessage());
            return false;
        }


        Logger::log($gateway, $amount, $transactionId, self::STAGE_ATTEMPT, Logger::COLOR_INFO . " Payment Started" . Logger::COLOR_RESET . " ");


        $response = $provider->purchase([
            'amount'        => $amount,
            'currency'      => $gateway->getCurrency(),
            'transactionId' => $transactionId,
            'card'          => $card
        ])->send();

        if ($response->isSuccessful()) {
            Logger::log($gateway, $amount, $transactionId, self::STAGE_SUCCESS, Logger::COLOR_SUCCESS . " Payment Successful" . Logger::COLOR_RESET . " ", $response->getData());
            return $response;
        } elseif ($response->isRedirect()) {
            Logger::log($gateway, $amount, $transactionId, self::STAGE_REDIRECT, Logger::COLOR_INFO2 . " Redirecting to Hosted " . Logger::COLOR_RESET . " ", $response->getData());
            return $response;
        } else {
            Logger::log($gateway, $amount, $transactionId, self::STAGE_FAILURE, Logger::COLOR_ERROR . " Failed Payment" . Logger::COLOR_RESET . " ", $response->getData());
            return $response;
        }
    }

    /**
     * @param PaymentGatewayInterface $gateway
     *
     * @return \Omnipay\Common\GatewayInterface
     */
    private static function getProvider($gateway)
    {
        switch ($gateway->getProvider()) {
            case self::PROVIDER_AUTHORIZENET_API:
                $provider = Omnipay::create(self::OMNIPAY_PROVIDER_AUTHORIZENET_API);
                $provider->setAuthName($gateway->login);
                $provider->setTransactionKey($gateway->transaction_key);
                $provider->setTestMode($gateway->isTest());
                break;
            case self::PROVIDER_AUTHORIZENET_HOSTED:
                $provider = Omnipay::create(self::OMNIPAY_PROVIDER_AUTHORIZENET_HOSTED);
                $provider->setAuthName($gateway->login);
                $provider->setTransactionKey($gateway->transaction_key);
                $provider->setTestMode($gateway->isTest());
                break;
            default:
                throw new \Exception('Invalid Payment Provider');
                break;
        }

        return $provider;
    }
}