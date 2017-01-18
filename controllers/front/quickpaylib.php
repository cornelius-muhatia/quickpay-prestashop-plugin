<?php
/**
* 2016 Quickpay
*
* NOTICE OF LICENSE
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
*  @author    Binary Limited <info@binary.co.ke>
*  @copyright 2016 Binary Limited
*  @license   http://www.gnu.org/licenses
*/

class QuickPay
{
    protected $username;
    protected $apikey;
    protected $requestBody;
    protected $requestURI;
    protected $responseBody;
    protected $responseInfo;
    protected $Headers;
    protected $HTTP_CODE_OK;
    protected $PAY_URL;
    const _SUCCESS_RCODE = 200;

    public function __construct($apiKey, $isTest = false)
    {
        $this->PAY_URL = ($isTest === true) ? "https://checkout-test.quickpay.co.ke/chargetoken"
                : "https://checkout.quickpay.co.ke/chargetoken";

        if (Tools::strlen($apiKey) == 0) {
            throw new QuickPayException('Please supply both username and apikey files. key ');
        } else {
            $this->apikey = $apiKey;
        }
    }

    /**
     *
     * @param type $referenceNo
     * @param type $orderInfo
     * @param type $amount
     * @param type $token
     * @param type $Currency
     */
    public function sendMessage($referenceNo, $orderInfo, $amount, $token, $Currency)
    {
        include('QuickPayException.php');
        if (empty($referenceNo) || empty($orderInfo) || empty($amount) || empty($token) || empty($Currency)) {
            throw new QuickPayException('Please supply both username and apikey files. ');
        } else {
            $params             = array(
                "reference" => $referenceNo, "orderinfo" => $orderInfo,
                "currency" => $Currency, "amount" => $amount,
                "userkey" => $this->apikey, "token" => $token
            );
//            Set up channel configurations
            $this->HTTP_CODE_OK = self::_SUCCESS_RCODE;
            $this->requestURI  = $this->PAY_URL;
            $this->requestBody = Tools::jsonEncode($params);
            $this->exeutePost($params);
            if ($this->responseInfo['http_code'] == self::_SUCCESS_RCODE) {
                $responseObject = Tools::jsonDecode($this->responseBody);
                return $responseObject;
            } else {
                throw new QuickPayException($this->responseBody);
            }
            return "Error";
        }
    }

    private function exeutePost($params)
    {
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
        curl_setopt($ch, CURLOPT_POST, 1);
        $Headers   = array();
        $Headers[] = 'SecureHash: '.
            base64_encode(
                hash_hmac("sha256", Tools::jsonEncode($params), $this->apikey, true)
            );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $Headers);
        $this->doExecute($ch);
    }

    /**
     *
     * @param type $curlHandle_
     * @throws Exeption
     */
    private function doExecute(&$curlHandle_)
    {
        try {
            $this->setCurlOpts($curlHandle_);
            $responseBody        = curl_exec($curlHandle_);
            $this->responseInfo = curl_getinfo($curlHandle_);
            $this->responseBody = $responseBody;
            curl_close($curlHandle_);
        } catch (Exeption $e) {
            curl_close($curlHandle_);
            throw $e;
        }
    }

    /**
     *
     * @param type $curlHandle_s
     */
    private function setCurlOpts(&$curlHandle_)
    {
        curl_setopt($curlHandle_, CURLOPT_TIMEOUT, 60);
        curl_setopt($curlHandle_, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle_, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curlHandle_, CURLOPT_URL, $this->requestURI);
        curl_setopt($curlHandle_, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle_, CURLOPT_TIMEOUT, 15);
        curl_setopt($curlHandle_, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curlHandle_, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }
}
