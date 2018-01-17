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
{if isset($products) && $products}
    {*define number of products per line in other page for desktop*}
    {if $page_name == 'index'}
        {assign var='nbItemsPerLine' value=5}
        {assign var='nbItemsPerLineTablet' value=5}
        {assign var='nbItemsPerLineMobile' value=5}
    {elseif $page_name !='index' && $page_name !='product'}
        {assign var='nbItemsPerLine' value=4}
        {assign var='nbItemsPerLineTablet' value=4}
        {assign var='nbItemsPerLineMobile' value=4}
    {/if}
    {*define numbers of product per line in other page for tablet*}
    {assign var='nbLi' value=$products|@count}
    {math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
    {math equation="nbLi/nbItemsPerLineTablet" nbLi=$nbLi nbItemsPerLineTablet=$nbItemsPerLineTablet assign=nbLinesTablet}
    <!-- Products list -->
    <ul{if isset($id) && $id} id="{$id}"{/if} class="product_list grid row{if isset($class) && $class} {$class}{/if}">
    {foreach from=$products item=product name=products}
        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}
        <li class="ajax_block_product{if $page_name == 'index' || $page_name == 'product'} col-sm-2-4{else} col-xs-3{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLine == 1 && $page_name !='index' && $page_name !='product'} first-in-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModulo)} last-line{/if}{if isset($slider) && $slider} product-slide{/if}">
            <div class="product-container {if !isset($slider) || !$slider}block_shadow{/if}" itemscope itemtype="https://schema.org/Product">
                <div class="left-block">
                    <div class="product-image-container">
                        <a class="product_img_link" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url">
                            <img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" {if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}"{/if} itemprop="image" />
                        </a>
                        {*
                        {if isset($quick_view) && $quick_view}
                            <a class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}">
                                <span>{l s='Quick view'}</span>
                            </a>
                        {/if}
                        Special prices offers
                        {if (!$PS_CATALOG_MODE && ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                            <div class="content_price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                                    <span itemprop="price" class="price product-price">
                                        {hook h="displayProductPriceBlock" product=$product type="before_price"}
                                        {if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
                                    </span>
                                    <meta itemprop="priceCurrency" content="{$currency->iso_code}" />
                                    {if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
                                        {hook h="displayProductPriceBlock" product=$product type="old_price"}
                                        <span class="old-price product-price">
                                            {displayWtPrice p=$product.price_without_reduction}
                                        </span>
                                        {if $product.specific_prices.reduction_type == 'percentage'}
                                            <span class="price-percent-reduction">-{$product.specific_prices.reduction * 100}%</span>
                                        {/if}
                                    {/if}
                                    {if $PS_STOCK_MANAGEMENT && isset($product.available_for_order) && $product.available_for_order && !isset($restricted_country_mode)}
                                        <span class="unvisible">
                                            {if ($product.allow_oosp || $product.quantity > 0)}
                                                    <link itemprop="availability" href="https://schema.org/InStock" />{if $product.quantity <= 0}{if $product.allow_oosp}{if isset($product.available_later) && $product.available_later}{$product.available_later}{else}{l s='In Stock'}{/if}{else}{l s='Out of stock'}{/if}{else}{if isset($product.available_now) && $product.available_now}{$product.available_now}{else}{l s='In Stock'}{/if}{/if}
                                            {elseif (isset($product.quantity_all_versions) && $product.quantity_all_versions > 0)}
                                                    <link itemprop="availability" href="https://schema.org/LimitedAvailability" />{l s='Product available with different options'}

                                            {else}
                                                    <link itemprop="availability" href="https://schema.org/OutOfStock" />{l s='Out of stock'}
                                            {/if}
                                        </span>
                                    {/if}
                                    {hook h="displayProductPriceBlock" product=$product type="price"}
                                    {hook h="displayProductPriceBlock" product=$product type="unit_price"}
                                {/if}
                            </div>
                        {/if}
                        *}

                        {if isset($product.is_new) && $product.is_new == 1}
                            <a class="new-box" href="{$product.link|escape:'html':'UTF-8'}">
                                <span class="new-label">{l s='New'}</span>
                            </a>
                        {/if}
                        {if (isset($product.has_free_gift) && $product.has_free_gift == 1)
                            || (isset($product.show_gift_label) && $product.show_gift_label == 1)}
                            <a class="sale-box" href="{$product.link|escape:'html':'UTF-8'}">
                                <span class="sale-label">{l s='Gift'}</span>
                            </a>
                        {/if}

                        {if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0 && (!isset($product.is_series) || $product.is_series == 0)}
                            {hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"}
                            {if $product.specific_prices.reduction_type == 'percentage'}
                                <a href="#" class="special-percentage-box">
                                    <span class="special-percentage-label">-{$product.specific_prices.reduction * 100}%</span>
                                </a>
                            {/if}
                        {/if}


                        {*
                        {if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
                        {/if}
                        *}

                    </div>
                    {if isset($product.is_virtual) && !$product.is_virtual}{hook h="displayProductDeliveryTime" product=$product}{/if}
                    {hook h="displayProductPriceBlock" product=$product type="weight"}
                </div>
                <div class="right-block">
                    <h5 itemprop="name">
                        {if isset($product.pack_quantity) && $product.pack_quantity}{$product.pack_quantity|intval|cat:' x '}{/if}
                        {if isset($product.is_series) && $product.is_series}
                            <a class="product-name" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
                                {$product.name|escape:'html':'UTF-8'}
                            </a>
                        {else}
                            <a class="product-name" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
                                {$product.name|escape:'html':'UTF-8'}
                            </a>
                        {/if}
                    </h5>
                    {* {hook h='displayProductListReviews' product=$product} *}
                    {if isset($product.description_short) && $product.description_short}
                        <p class="product-desc" itemprop="description">
                            {$product.description_short|strip_tags:'UTF-8'|truncate:360:'...'}
                        </p>
                    {/if}
                    {if isset($product.is_series) && $product.is_series}
                        {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                        <div class="content_price">
                            {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                                {hook h="displayProductPriceBlock" product=$product type='before_price'}
                                <!-- <div class="pull-left"> -->
                                    <span class="price-label">{l s='Price from'}</span>
                                <!-- </div> -->
                                <!-- <div class="pull-right"> -->
                                {if ((isset($product.price_before_reduction)) && ($product.price_before_reduction > $product.price_min))}
                                    {hook h="displayProductPriceBlock" product=$product type="old_price"}
                                    <span class="old-price product-price">
                                        {displayWtPrice p=$product.price_without_reduction}
                                    </span>
                                {/if}
                                    <span class="price product-price">
                                        {if $product.is_series}{convertPrice price=$product.price_min}{else}{convertPrice price=$product.price}{/if}
                                    </span>
                                <!-- </div> -->
                                <!-- <span class="dots"></span> -->
                                {hook h="displayProductPriceBlock" product=$product type="price"}
                                {hook h="displayProductPriceBlock" product=$product type="unit_price"}
                                {hook h="displayProductPriceBlock" product=$product type='after_price'}
                            {/if}
                        </div>
                        {/if}
                    {else}
                        {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                        <div class="content_price">
                            {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                                {hook h="displayProductPriceBlock" product=$product type='before_price'}

                                <span class="price-label">{l s='Price'}</span>

                                {if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
                                    {hook h="displayProductPriceBlock" product=$product type="old_price"}
                                    <span class="old-price product-price">
                                        {displayWtPrice p=$product.price_without_reduction}
                                    </span>
                                {/if}

                                <span class="price product-price">
                                    {if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
                                </span>

                                {hook h="displayProductPriceBlock" product=$product type="price"}
                                {hook h="displayProductPriceBlock" product=$product type="unit_price"}
                                {hook h="displayProductPriceBlock" product=$product type='after_price'}
                            {/if}
                        </div>
                        {/if}
                        {*
                        {if isset($product.features) && $product.features}
                            <div class="features-block">
                                {foreach from=$product.features item=feature}
                                    <div class="feature">
                                        <div class="pull-left">
                                            <span class="feature-label">{$feature.name}</span>
                                        </div>
                                        <div class="pull-right">
                                            <span class="feature-value">
                                                {$feature.value}
                                            </span>
                                        </div>
                                        <span class="dots"></span>
                                    </div>
                                {/foreach}
                            </div>
                        {/if}
                        *}
                        {if (!$PS_CATALOG_MODE && $PS_STOCK_MANAGEMENT && ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                            {if isset($product.available_for_order) && $product.available_for_order && !isset($restricted_country_mode)}
                                <div class="availability-block">
                                    {include file="$tpl_dir./_availability_block.tpl" allow_oosp=$product.allow_oosp quantity=$product.quantity}
                                </div>
                            {/if}
                        {/if}
                        {if isset($product.color_list) && $product.color_list}
                            <div class="color-list-container">{$product.color_list}</div>
                        {/if}
                        <div class="product-flags">
                            {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                                {if isset($product.online_only) && $product.online_only}
                                    <span class="online_only">{l s='Online only'}</span>
                                {/if}
                            {/if}
                            {if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
                                {elseif isset($product.reduction) && $product.reduction && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
                                    <span class="discount">{l s='Reduced price!'}</span>
                            {/if}
                        </div>
                        <div class="button-container">
                            {if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product.available_for_order && !isset($restricted_country_mode) && $product.customizable != 2 && !$PS_CATALOG_MODE}
                                {if (!isset($product.customization_required) || !$product.customization_required) && ($product.allow_oosp || $product.quantity > 0)}
                                    {capture}add=1&amp;id_product={$product.id_product|intval}{if isset($product.id_product_attribute) && $product.id_product_attribute}&amp;ipa={$product.id_product_attribute|intval}{/if}{if isset($static_token)}&amp;token={$static_token}{/if}{/capture}
                                    <a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart'}" data-id-product-attribute="{$product.id_product_attribute|intval}" data-id-product="{$product.id_product|intval}" data-minimal_quantity="{if isset($product.product_attribute_minimal_quantity) && $product.product_attribute_minimal_quantity >= 1}{$product.product_attribute_minimal_quantity|intval}{else}{$product.minimal_quantity|intval}{/if}">
                                        <span>{l s='Add to cart'}</span>
                                    </a>
                                {else}
                                    <span class="button ajax_add_to_cart_button btn btn-default disabled">
                                        <span>{l s='Add to cart'}</span>
                                    </span>
                                {/if}
                            {/if}
                            {*
                            <a class="button lnk_view btn btn-default" href="{$product.link|escape:'html':'UTF-8'}" title="{l s='View'}">
                                <span>{if (isset($product.customization_required) && $product.customization_required)}{l s='Customize'}{else}{l s='More'}{/if}</span>
                            </a>
                            *}
                        </div>
                    {/if}
                </div>
                {if $page_name != 'index'}
                    {if !isset($product.is_series) || !$product.is_series}
                        <div class="functional-buttons clearfix">
                            {hook h='displayProductListFunctionalButtons' product=$product}
                            {if isset($comparator_max_item) && $comparator_max_item}
                                <div class="compare">
                                    <a class="add_to_compare" href="{$product.link|escape:'html':'UTF-8'}" data-id-product="{$product.id_product}">{l s='Add to Compare'}</a>
                                </div>
                            {/if}
                        </div>
                    {/if}
                {/if}
            </div><!-- .product-container> -->
        </li>
    {/foreach}
    </ul>
{addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
{addJsDefL name=max_item}{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}{/addJsDefL}
{addJsDef comparator_max_item=$comparator_max_item}
{addJsDef comparedProductsIds=$compared_products}
{/if}