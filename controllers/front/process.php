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

class QuickpaycheckoutProcessModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();
        include('quickpaylib.php');
        $cart   = $this->context->cart;
        $extras = array();
        //$message = '';
        $amount = 0;
        //check if the cart has been loaded
        if ($cart->id == '') {
            Tools::redirect('index.php?controller=order');
        }
        //check if the customer is loaded
        if (!Validate::isLoadedObject($this->context->customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        try {
            //$customer = new Customer($cart->id_customer);
            $details     = $cart->getSummaryDetails();
            $apikey      = Configuration::get('QUICKPAYCHECKOUT_PRIVATE_KEY');
            $currency    = $this->context->currency->iso_code;
            $token       = Tools::getValue('qpToken');
            //convert amount to the smallest unit currency
            $amount      = $details['total_price'];
            $referenceNo = time();
            $orderInfo   = "Cart id ".$cart->id;

            //Perform payment
            $isTest   = (Configuration::get('QUICKPAYCHECKOUT_ENVIRONMENT') == 0) ? true : false;
            $gateway  = new QuickPay($apikey, $isTest);
            $response = $gateway->sendMessage($referenceNo, $orderInfo, $amount, $token, $currency);

            $extras['transaction_id'] = $response->data->data->transactionNo;
            $status_code              = (int) $response->code;
            $extras['response_code']  = $status_code;
            $message                  = $response->data->message;

            $amount           = $details['total_price'];
            $order            = new Order((int) Order::getOrderByCartId($cart->id));
            $order->reference = $referenceNo;

            if ($status_code == 0) {//check if the request was successful
                if ($cart->OrderExists()) {
                    $new_history           = new OrderHistory();
                    $new_history->id_order = (int) $order->id;
                    $new_history->changeIdOrderState((int) Configuration::get('PS_OS_PAYMENT'), $order, true);
                    $new_history->addWithemail(true);
                }
                $this->module->validateOrder(
                    $cart->id,
                    Configuration::get('PS_OS_PAYMENT'),
                    $amount,
                    $this->module->displayName,
                    $message,
                    $extras,
                    null,
                    false,
                    $this->context->customer->secure_key,
                    null
                );
            } elseif ($status_code > 1 && $status_code < 10) { //if the bank declined the request record
                PrestaShopLogger::addLog(
                    $response->data->message,
                    1,
                    $status_code,
                    $this->module->displayName,
                    $this->module->id
                );
                Tools::redirect(
                    $this->context->link->getPageLink(
                        'order',
                        true,
                        null,
                        array(
                            'quickpaycheckout_error' => 'Your card issuer declined the payment request '.
                            '(Details: '.$response->data->message.')',
                            'step' => 3
                        )
                    )
                );
            } else {//log response error
                PrestaShopLogger::addLog(
                    $response->data->message,
                    1,
                    $status_code,
                    $this->module->displayName,
                    $this->module->id
                );
                Tools::redirect(
                    $this->context->link->getPageLink(
                        'order',
                        true,
                        null,
                        array(
                            'quickpaycheckout_error' => 'Sorry we failed to process your request '
                            . '(Details: '.$response->data->message.')',
                            'step' => 3)
                    )
                );
            }
        } catch (Exception $ex) {//log transaction exception
            PrestaShopLogger::addLog(
                $ex->getMessage(),
                1,
                $ex->getCode(),
                $this->module->displayName,
                $this->module->id
            );
            Tools::redirect(
                $this->context->link->getPageLink(
                    'order',
                    true,
                    null,
                    array(
                        'quickpaycheckout_error' => 'Sorry an internal server error occured please try again',
                        'step' => 3)
                )
            );
        }
        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart='
            .$cart->id
            .'&id_module='
            .$this->module->id
            .'&id_order='
            .$this->module->currentOrder
            .'&key='
            .$this->context->customer->secure_key
        );
    }
}
