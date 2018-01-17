{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture name=path}{l s='Your shopping cart'}{/capture}

<h1 id="cart_title" class="page-heading">{l s='Shopping-cart summary'}</h1>

{assign var='current_step' value='summary'}
{include file="$tpl_dir./order-steps.tpl"}
{include file="$tpl_dir./errors.tpl"}

{if isset($empty)}
    <p class="alert alert-warning">{l s='Your shopping cart is empty.'}</p>
{elseif $PS_CATALOG_MODE}
    <p class="alert alert-warning">{l s='This store has not accepted your new order.'}</p>
{else}
    <p id="emptyCartWarning" class="alert alert-warning unvisible">{l s='Your shopping cart is empty.'}</p>
    {if isset($lastProductAdded) AND $lastProductAdded}
        <div class="cart_last_product">
            <div class="cart_last_product_header">
                <div class="left">{l s='Last product added'}</div>
            </div>
            <a class="cart_last_product_img" href="{$link->getProductLink($lastProductAdded.id_product, $lastProductAdded.link_rewrite, $lastProductAdded.category, null, null, $lastProductAdded.id_shop)|escape:'html':'UTF-8'}">
                <img src="{$link->getImageLink($lastProductAdded.link_rewrite, $lastProductAdded.id_image, 'small_default')|escape:'html':'UTF-8'}" alt="{$lastProductAdded.name|escape:'html':'UTF-8'}"/>
            </a>
            <div class="cart_last_product_content">
                <p class="product-name">
                    <a href="{$link->getProductLink($lastProductAdded.id_product, $lastProductAdded.link_rewrite, $lastProductAdded.category, null, null, null, $lastProductAdded.id_product_attribute)|escape:'html':'UTF-8'}">
                        {$lastProductAdded.name|escape:'html':'UTF-8'}
                    </a>
                </p>
                {if isset($lastProductAdded.attributes) && $lastProductAdded.attributes}
                    <small>
                        <a href="{$link->getProductLink($lastProductAdded.id_product, $lastProductAdded.link_rewrite, $lastProductAdded.category, null, null, null, $lastProductAdded.id_product_attribute)|escape:'html':'UTF-8'}">
                            {$lastProductAdded.attributes|escape:'html':'UTF-8'}
                        </a>
                    </small>
                {/if}
            </div>
        </div>
    {/if}
    {assign var='total_discounts_num' value="{if $total_discounts != 0}1{else}0{/if}"}
    {assign var='use_show_taxes' value="{if $use_taxes && $show_taxes}2{else}0{/if}"}
    {assign var='total_wrapping_taxes_num' value="{if $total_wrapping != 0}1{else}0{/if}"}
    {* eu-legal *}
    {hook h="displayBeforeShoppingCartBlock"}
    <div id="order-detail-content" class="block_shadow table_block table-responsive">
        <table id="cart_summary" class="table table-bordered {if $PS_STOCK_MANAGEMENT}stock-management-on{else}stock-management-off{/if}">
            <thead>
                <tr>
                    <th class="cart_product first_item">{l s='Product'}</th>
                    {assign var='col_span_subtotal' value='3'}
                    <th class="cart_unit item">{l s='Unit price'}</th>
                    <th class="cart_quantity item">{l s='Qty'}</th>
                    <th class="cart_total item">{l s='Total'}</th>
                    <th class="cart_delete last_item">&nbsp;</th>
                </tr>
            </thead>
            <tfoot>
                {assign var='rowspan_total' value=2+$total_discounts_num+$total_wrapping_taxes_num}

                {if $use_taxes && $show_taxes && $total_tax != 0}
                    {assign var='rowspan_total' value=$rowspan_total+1}
                {/if}

                {if $priceDisplay != 0}
                    {assign var='rowspan_total' value=$rowspan_total+1}
                {/if}

                {if $total_shipping_tax_exc <= 0 && (!isset($isVirtualCart) || !$isVirtualCart) && $free_ship}
                    {assign var='rowspan_total' value=$rowspan_total+1}
                {else}
                    {if $use_taxes && $total_shipping_tax_exc != $total_shipping}
                        {if $priceDisplay && $total_shipping_tax_exc > 0}
                            {assign var='rowspan_total' value=$rowspan_total+1}
                        {elseif $total_shipping > 0}
                            {assign var='rowspan_total' value=$rowspan_total+1}
                        {/if}
                    {elseif $total_shipping_tax_exc > 0}
                        {assign var='rowspan_total' value=$rowspan_total+1}
                    {/if}
                {/if}

                <tr class="cart_delivery_block">
                    <td colspan="5">
                        <div class="shipping_methods_block">
                            {include file="$tpl_dir./quick-order-carrier.tpl"}
                        </div>
                        <div class="arrow-right"></div>
                        <div class="shipping_cost_block">
                            {if ($total_shipping_tax_exc <= 0 && (!isset($isVirtualCart) || !$isVirtualCart) && $free_shipping) || $carrier->is_free}
                                <span class="shipping_label">{l s='Total shipping'}</span>
                                <span class="price" id="total_shipping">{l s='Free shipping!'}</span>
                            {else}
                                {if $use_taxes && $total_shipping_tax_exc != $total_shipping}
                                    {if $priceDisplay}
                                        <span class="shipping_label" style="display:none">{if $display_tax_label}{l s='Total shipping (tax excl.)'}{else}{l s='Total shipping'}{/if}</span>
                                        {*<span class="price" id="total_shipping">{displayPrice price=$total_shipping_tax_exc}</span>*}
                                        <span class="price wrap-text" id="total_shipping">{l s='Стоимость доставки можно рассчитать через раздел "Доставка и оплата"'}</span>
                                    {else}
                                        <span class="shipping_label" style="display:none">{if $display_tax_label}{l s='Total shipping (tax incl.)'}{else}{l s='Total shipping'}{/if}</span>
                                        {*<span class="price" id="total_shipping" >{displayPrice price=$total_shipping}</span>*}
                                        <span class="price wrap-text" id="total_shipping">{l s='Стоимость доставки можно рассчитать через раздел "Доставка и оплата"'}</span>
                                    {/if}
                                {else}
                                    {if ($total_shipping_tax_exc > 0)}
                                        <span class="shipping_label">{l s='Total shipping'}</span>
                                        <span class="price" id="total_shipping">{displayPrice price=$total_shipping_tax_exc}</span>
                                    {else}
                                        <span class="shipping_label" style="display:none">{l s='Total shipping'}</span>
                                        <span class="price wrap-text" id="total_shipping">{l s='Стоимость доставки можно рассчитать через раздел "Доставка и оплата"'}</span>
                                    {/if}
                                {/if}
                            {/if}
                        </div>
                    </td>
                </tr>

                {if $voucherAllowed}
                    <tr class="voucher_block">
                        <td colspan="5">
                            {if isset($errors_discount) && $errors_discount}
                                <ul class="alert alert-danger">
                                    {foreach $errors_discount as $k=>$error}
                                        <li>{$error|escape:'html':'UTF-8'}</li>
                                    {/foreach}
                                </ul>
                            {/if}
                            <form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
                                <fieldset>
                                    <div class="input-group">
                                        <span class="input-group-addon voucher_title">{l s='Do you have voucher code?'}</span>
                                        <input type="text" class="discount_name form-control" id="discount_name" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
                                    </div>
                                    <input type="hidden" name="submitDiscount" />
                                    <button type="submit" name="submitAddDiscount" class="btn btn-default button-small"><span>{l s='OK'}</span></button>
                                </fieldset>
                            </form>
                            {*
                            {if $displayVouchers}
                                <p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:'}</p>
                                <div id="display_cart_vouchers">
                                    {foreach $displayVouchers as $voucher}
                                        {if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
                                    {/foreach}
                                </div>
                            {/if}
                            *}
                            <span class="additional-text">{l s="Введите код указанный на лицевой стороне дисконтной карты либо подарочного сертификата"}</span>
                        </td>
                    </tr>
                {/if}


            {*
                <tr class="cart_total_price">
                    <td colspan="5" class="total_price_container text-right">
                        <div class="total_price_block">
                            <span class="total_text">Сумма заказа:</span>
                            <span id="total_product" class="price product-price">{displayPrice price=$total_products}</span>
                        </div>
                    </td>
                </tr>

                <tr class="cart_total_price cart_total_voucher{if $total_discounts == 0} unvisible{/if}">
                    <td colspan="5" class="total_price_container text-right">
                        <div class="total_price_block">
                            <span class="total_text">Сумма скидки: </span>
                            <span id="total_discount" class="price price-discount product-price">
                                {assign var='total_discounts_negative' value=$total_discounts_tax_exc * -1}
                                {displayPrice price=$total_discounts_negative}
                            </span>
                        </div>
                    </td>
                </tr>
            *}


                <tr class="cart_total_price">
                    <td colspan="5" class="total_price_container text-right">
                        <div class="total_price_block">
                            <span class="total_text">{l s='Total sum'}</span>

                            {if $use_taxes}
                                <span id="total_price" class="price product-price">{displayPrice price=$total_price}</span>
                            {else}
                                <span id="total_price" class="price product-price">{displayPrice price=$total_price_without_tax}</span>
                            {/if}
                        </div>
                    </td>
                </tr>
            </tfoot>

            <tbody>
                {assign var='odd' value=0}
                {assign var='have_non_virtual_products' value=false}
                {foreach $products as $product}
                    {if $product.is_virtual == 0}
                        {assign var='have_non_virtual_products' value=true}
                    {/if}
                    {assign var='productId' value=$product.id_product}
                    {assign var='productAttributeId' value=$product.id_product_attribute}
                    {assign var='quantityDisplayed' value=0}
                    {assign var='odd' value=($odd+1)%2}
                    {assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId) || count($gift_products)}
                    {* Display the product line *}
                    {include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
                    {* Then the customized datas ones*}
                {/foreach}
                {assign var='last_was_odd' value=$product@iteration%2}
                {foreach $gift_products as $product}
                    {assign var='productId' value=$product.id_product}
                    {assign var='productAttributeId' value=$product.id_product_attribute}
                    {assign var='quantityDisplayed' value=0}
                    {assign var='odd' value=($product@iteration+$last_was_odd)%2}
                    {assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId)}
                    {assign var='cannotModify' value=1}
                    {* Display the gift product line *}
                    {include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
                {/foreach}
            </tbody>


            {if sizeof($discounts)}
                <tbody>
                    {foreach $discounts as $discount}
                    {if ((float)$discount.value_real == 0 && $discount.free_shipping != 1) || ((float)$discount.value_real == 0 && $discount.code == '')}
                        {continue}
                    {/if}
                        <tr class="cart_discount {if $discount@last}last_item{elseif $discount@first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
                            <td class="cart_discount_name" colspan="3">{$discount.name}</td>
                            <td class="cart_discount_price">
                                <span class="price-discount price">{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}</span>
                            </td>
                            <td class="price_discount_del text-center">
                                {if strlen($discount.code)}
                                    <a
                                        href="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}?deleteDiscount={$discount.id_discount}"
                                        class="price_discount_delete"
                                        title="{l s='Delete'}">
                                        <!-- <i class="icon-trash"></i> -->
                                    </a>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            {/if}

        </table>
    </div> <!-- end order-detail-content -->

    {if $show_option_allow_separate_package}
    <p>
        <label for="allow_seperated_package" class="checkbox inline">
            <input type="checkbox" name="allow_seperated_package" id="allow_seperated_package" {if $cart->allow_seperated_package}checked="checked"{/if} autocomplete="off"/>
            {l s='Send available products first'}
        </label>
    </p>
    {/if}

    {* Define the style if it doesn't exist in the PrestaShop version*}
    {* Will be deleted for 1.5 version and more *}
    {if !isset($addresses_style)}
        {$addresses_style.company = 'address_company'}
        {$addresses_style.vat_number = 'address_company'}
        {$addresses_style.firstname = 'address_name'}
        {$addresses_style.lastname = 'address_name'}
        {$addresses_style.address1 = 'address_address1'}
        {$addresses_style.address2 = 'address_address2'}
        {$addresses_style.city = 'address_city'}
        {$addresses_style.country = 'address_country'}
        {$addresses_style.phone = 'address_phone'}
        {$addresses_style.phone_mobile = 'address_phone_mobile'}
        {$addresses_style.alias = 'address_title'}
    {/if}
    {if !$advanced_payment_api && ((!empty($delivery_option) && (!isset($isVirtualCart) || !$isVirtualCart)) OR $delivery->id || $invoice->id) && !$opc}
        <div class="order_delivery clearfix row">
            {if !isset($formattedAddresses) || (count($formattedAddresses.invoice) == 0 && count($formattedAddresses.delivery) == 0) || (count($formattedAddresses.invoice.formated) == 0 && count($formattedAddresses.delivery.formated) == 0)}
                {if $delivery->id}
                    <div class="col-xs-12 col-sm-6"{if !$have_non_virtual_products} style="display: none;"{/if}>
                        <ul id="delivery_address" class="address item box">
                            <li><h3 class="page-subheading">{l s='Delivery address'}&nbsp;<span class="address_alias">({$delivery->alias})</span></h3></li>
                            {if $delivery->company}<li class="address_company">{$delivery->company|escape:'html':'UTF-8'}</li>{/if}
                            <li class="address_name">{$delivery->firstname|escape:'html':'UTF-8'} {$delivery->lastname|escape:'html':'UTF-8'}</li>
                            <li class="address_address1">{$delivery->address1|escape:'html':'UTF-8'}</li>
                            {if $delivery->address2}<li class="address_address2">{$delivery->address2|escape:'html':'UTF-8'}</li>{/if}
                            <li class="address_city">{$delivery->postcode|escape:'html':'UTF-8'} {$delivery->city|escape:'html':'UTF-8'}</li>
                            <li class="address_country">{$delivery->country|escape:'html':'UTF-8'} {if $delivery_state}({$delivery_state|escape:'html':'UTF-8'}){/if}</li>
                        </ul>
                    </div>
                {/if}
                {if $invoice->id}
                    <div class="col-xs-12 col-sm-6">
                        <ul id="invoice_address" class="address alternate_item box">
                            <li><h3 class="page-subheading">{l s='Invoice address'}&nbsp;<span class="address_alias">({$invoice->alias})</span></h3></li>
                            {if $invoice->company}<li class="address_company">{$invoice->company|escape:'html':'UTF-8'}</li>{/if}
                            <li class="address_name">{$invoice->firstname|escape:'html':'UTF-8'} {$invoice->lastname|escape:'html':'UTF-8'}</li>
                            <li class="address_address1">{$invoice->address1|escape:'html':'UTF-8'}</li>
                            {if $invoice->address2}<li class="address_address2">{$invoice->address2|escape:'html':'UTF-8'}</li>{/if}
                            <li class="address_city">{$invoice->postcode|escape:'html':'UTF-8'} {$invoice->city|escape:'html':'UTF-8'}</li>
                            <li class="address_country">{$invoice->country|escape:'html':'UTF-8'} {if $invoice_state}({$invoice_state|escape:'html':'UTF-8'}){/if}</li>
                        </ul>
                    </div>
                {/if}
            {else}
                {foreach from=$formattedAddresses key=k item=address}
                    <div class="col-xs-12 col-sm-6"{if $k == 'delivery' && !$have_non_virtual_products} style="display: none;"{/if}>
                        <ul class="address {if $address@last}last_item{elseif $address@first}first_item{/if} {if $address@index % 2}alternate_item{else}item{/if} box">
                            <li>
                                <h3 class="page-subheading">
                                    {if $k eq 'invoice'}
                                        {l s='Invoice address'}
                                    {elseif $k eq 'delivery' && $delivery->id}
                                        {l s='Delivery address'}
                                    {/if}
                                    {if isset($address.object.alias)}
                                        <span class="address_alias">({$address.object.alias})</span>
                                    {/if}
                                </h3>
                            </li>
                            {foreach $address.ordered as $pattern}
                                {assign var=addressKey value=" "|explode:$pattern}
                                {assign var=addedli value=false}
                                {foreach from=$addressKey item=key name=foo}
                                {$key_str = $key|regex_replace:AddressFormat::_CLEANING_REGEX_:""}
                                    {if isset($address.formated[$key_str]) && !empty($address.formated[$key_str])}
                                        {if (!$addedli)}
                                            {$addedli = true}
                                            <li><span class="{if isset($addresses_style[$key_str])}{$addresses_style[$key_str]}{/if}">
                                        {/if}
                                        {$address.formated[$key_str]|escape:'html':'UTF-8'}
                                    {/if}
                                    {if ($smarty.foreach.foo.last && $addedli)}
                                        </span></li>
                                    {/if}
                                {/foreach}
                            {/foreach}
                        </ul>
                    </div>
                {/foreach}
            {/if}
        </div>
    {/if}
    {*
    <div id="HOOK_SHOPPING_CART">{$HOOK_SHOPPING_CART}</div>
    <p class="cart_navigation clearfix">
        {if !$opc}
            <a  href="{if $back}{$link->getPageLink('order', true, NULL, 'step=1&amp;back={$back}')|escape:'html':'UTF-8'}{else}{$link->getPageLink('order', true, NULL, 'step=1')|escape:'html':'UTF-8'}{/if}" class="button btn btn-default standard-checkout button-medium" title="{l s='Proceed to checkout'}">
                <span>{l s='Proceed to checkout'}<i class="icon-chevron-right right"></i></span>
            </a>
        {/if}
        <a href="{if (isset($smarty.server.HTTP_REFERER) && ($smarty.server.HTTP_REFERER == $link->getPageLink('order', true) || $smarty.server.HTTP_REFERER == $link->getPageLink('order-opc', true) || strstr($smarty.server.HTTP_REFERER, 'step='))) || !isset($smarty.server.HTTP_REFERER)}{$link->getPageLink('index')}{else}{$smarty.server.HTTP_REFERER|escape:'html':'UTF-8'|secureReferrer}{/if}" class="button-exclusive btn btn-default" title="{l s='Continue shopping'}">
            <i class="icon-chevron-left"></i>{l s='Continue shopping'}
        </a>
    </p>
    <div class="clear"></div>
    <div class="cart_navigation_extra">
        <div id="HOOK_SHOPPING_CART_EXTRA">{if isset($HOOK_SHOPPING_CART_EXTRA)}{$HOOK_SHOPPING_CART_EXTRA}{/if}</div>
    </div>
    *}
{strip}
{addJsDef deliveryAddress=$cart->id_address_delivery|intval}
{addJsDefL name=txtProduct}{l s='product' js=1}{/addJsDefL}
{addJsDefL name=txtProducts}{l s='products' js=1}{/addJsDefL}
{/strip}
{/if}
