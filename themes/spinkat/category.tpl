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
{include file="$tpl_dir./errors.tpl"}
{if isset($category)}
    {if $category->id AND $category->active}
        <h1 class="page-heading{if (isset($subcategories) && !$products) || (isset($subcategories) && $products) || !isset($subcategories) && $products} product-listing{/if}">
            <span class="cat-name">
                {if isset($category->title_for_seo_h1) && $category->title_for_seo_h1}
                    {$category->title_for_seo_h1|escape:'html':'UTF-8'}
                {else}
                    {$category->name|escape:'html':'UTF-8'}
                {/if}
            </span>
        </h1>

        {hook h="displayBrandsOnCategoryPage" category_id=$category->id}

        {if isset($subcategories)}
            {if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories) }
            <!-- Subcategories -->
            <div id="subcategories">
                <div class="subcategory-heading display-on-mobile block_shadow">{l s='Subcategories'}</div>

                <div class="block_content">
                    <ul class="clearfix">
                    {foreach from=$subcategories item=subcategory}
                        <li>
                            <div class="subcategory-image">
                                <a href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="img">
                                {if $subcategory.id_image}
                                    <img class="replace-2x" src="{$link->getCatImageLink($subcategory.link_rewrite, $subcategory.id_image, 'medium_default')|escape:'html':'UTF-8'}" alt="" width="{$mediumSize.width}" height="{$mediumSize.height}" />
                                {else}
                                    <img class="replace-2x" src="{$img_cat_dir}{$lang_iso}-default-medium_default.jpg" alt="" width="{$mediumSize.width}" height="{$mediumSize.height}" />
                                {/if}
                            </a>
                            </div>
                            <h5><a class="subcategory-name" href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}">{$subcategory.name|escape:'html':'UTF-8'}</a></h5>
                            {if $subcategory.description}
                                <div class="cat_desc">{$subcategory.description}</div>
                            {/if}
                        </li>
                    {/foreach}
                    </ul>
                </div>
            </div>
            {/if}
        {/if}

        <div id="blocklayered_copy_mobile" class="display-on-mobile"></div>

        {if $products}
            <div class="content_sortPagiBar clearfix">
                <div class="sortPagiBar clearfix">
                    <div class="pull-left">
                        {include file="./product-sort.tpl"}
                    </div>
                    <div class="pull-right">
                        {include file="./product-compare.tpl"}
                    </div>
                </div>
            </div>
            {include file="./product-list.tpl" products=$products}
            <div class="content_sortPagiBar">
                <div class="bottom-pagination-content clearfix">
                    {*{include file="./product-compare.tpl" paginationId='bottom'}*}
                    {include file="./pagination.tpl" paginationId='bottom'}
                </div>
            </div>
        {/if}
    {elseif $category->id}
        <p class="alert alert-warning">{l s='This category is currently unavailable.'}</p>
    {/if}
{/if}
