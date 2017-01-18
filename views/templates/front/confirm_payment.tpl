{**
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
*}

{capture name=path}Quickpay Checkout{/capture}
<h1 id="cart_title" class="page-heading">{l s='Shopping-cart summary'  mod='quickpaycheckout'}
			<span class="heading-counter">{l s='Quickpay Checkout'  mod='quickpaycheckout'}
		</span>
	</h1> 

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<table class="table table-bordered table-hover">
  <tr><th>Product Name</th><th>Attributes</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr>
{foreach from=$productDetails['products'] item=product}
<tr><td class="text-right">{$product['name']|escape:'htmlall':'UTF-8'}</td><td class="text-right">{$product['attributes']|escape:'htmlall':'UTF-8'}</td><td class="text-right">{$product['cart_quantity']|escape:'htmlall':'UTF-8'}</td><td class="text-right">{$product['price']|escape:'htmlall':'UTF-8'}</td><td class="text-right">{$product['total']|escape:'htmlall':'UTF-8'}</td></tr>
{/foreach}
<!-- <tr><td colspan="4" class="text-right"><strong>Tax</strong></td><td class="text-right">{$currency_code|escape:'htmlall':'UTF-8'}.{$productDetails['total_tax']|escape:'htmlall':'UTF-8'}</td></tr> -->
<tr><td colspan="4" class="text-right"><strong>Totals (Inc. Tax)</strong></td><td class="text-right">{$currency_code|escape:'htmlall':'UTF-8'}.{$amount|escape:'htmlall':'UTF-8'}</td></tr>
</table>
<form action="{$link->getModuleLink('quickpaycheckout', 'process')|escape:'htmlall':'UTF-8'}" method="POST" id="qp-merchant-form"> 
                <script src="{$js_url|escape:'htmlall':'UTF-8'}" 
                        req-key="{$public_key|escape:'htmlall':'UTF-8'}" 
                        req-amount="{$amount|escape:'htmlall':'UTF-8'}" 
                        req-store="{$merchant_name|escape:'htmlall':'UTF-8'}" 
                        req-desc="{$description|escape:'htmlall':'UTF-8'}" 
                        req-button="{$button_text|escape:'htmlall':'UTF-8'}" 
                        req-image="{$icon|escape:'htmlall':'UTF-8'}"
                        req-currency="{$currency_code|escape:'htmlall':'UTF-8'}" 
                        type="text/javascript">
                </script> 
            </form>