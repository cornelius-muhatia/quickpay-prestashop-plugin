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

class QuickpaycheckoutPaymentModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();
        $cart = $this->context->cart;
        if ($cart->id == '') {
            Tools::redirect('index.php?controller=order');
        }
        $icon_url = $this->context->smarty->tpl_vars['base_dir']->value.
            'modules/quickpaycheckout/views/img/'.
            Configuration::get('QUICKPAYCHECKOUT_ICON');
        $js_url = "https://checkout".
            ((Configuration::get('QUICKPAYCHECKOUT_ENVIRONMENT') == 0) ? '-test' : '').
            ".quickpay.co.ke/js";
        $this->context->smarty->assign(
            array(
                "merchant_name" => Configuration::get('QUICKPAYCHECKOUT_NAME'),
                "public_key" => Configuration::get('QUICKPAYCHECKOUT_PUBLIC_KEY'),
                "amount" => (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2),
                "total_tax" => $cart->getTaxesAverageUsed($cart->id),
                "currency_code" => $this->context->currency->iso_code,
                "description" => Configuration::get('QUICKPAYCHECKOUT_DESC'),
                "button_text" => Configuration::get('QUICKPAYCHECKOUT_BUTTON_TEXT'),
                "icon" => $icon_url,
                "js_url" => $js_url,
                "products" => $cart->getProducts(),
                "productDetails" => $cart->getSummaryDetails()
            )
        );
        $this->setTemplate('confirm_payment.tpl');
    }
}
