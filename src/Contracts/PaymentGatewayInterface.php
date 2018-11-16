<?php

namespace Tomahawk\OmnipayWrapper\Contracts;

interface PaymentGatewayInterface
{
    public function getClientName();

    /**
     * @return mixed
     *
     * This is to store the payment details for statistical purposes. i.e. amount, the chosen provider, ip, success state, etc.
     */
    public function logPayment($gateway, $amount, $transactionId, $stage, $response = null);

    public function getProvider();

    public function getCurrency();

    /** @return boolean */
    public function isTest();
}

