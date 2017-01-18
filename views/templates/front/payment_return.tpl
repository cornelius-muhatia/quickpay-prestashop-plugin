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

{if $status == 2}
<div class="alert alert-success">
    {l s='Your order was placed successfully.' mod='quickpaycheckout'}
</div>
{/if}
{if $status == 8}
<div class="alert alert-danger">
    {l s= 'Sorry we enable to process your request please try again if the problem persists please inform us' mod='quickpaycheckout'}
</div>
{/if}