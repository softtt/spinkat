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

{capture name=path}{l s='Search'}{/capture}

<h1
{if isset($instant_search) && $instant_search}id="instant_search_results"{/if}
class="page-heading {if !isset($instant_search) || (isset($instant_search) && !$instant_search)} product-listing{/if}">
    {l s='Search by query'}&nbsp;
    {if $nbProducts > 0}
        <span class="lighter">
            "{if isset($search_query) && $search_query}{$search_query|escape:'html':'UTF-8'}{elseif $search_tag}{$search_tag|escape:'html':'UTF-8'}{elseif $ref}{$ref|escape:'html':'UTF-8'}{/if}"
        </span>
    {/if}
    {if isset($instant_search) && $instant_search}
        <a href="#" class="close">
            {l s='Return to the previous page'}
        </a>
    {else}
        <span class="heading-counter">
            {l s='Result has been found: %d' sprintf=$nbProducts|intval}
        </span>
    {/if}
</h1>

{include file="$tpl_dir./errors.tpl"}
{if !$nbProducts}
    <p class="alert alert-warning">
        {if isset($search_query_is_short) && $search_query_is_short}
            Поисковый запрос должен быть больше 3 символов
        {elseif isset($search_query) && $search_query}
            {l s='No results were found for your search'}&nbsp;"{if isset($search_query)}{$search_query|escape:'html':'UTF-8'}{/if}"
        {elseif isset($search_tag) && $search_tag}
            {l s='No results were found for your search'}&nbsp;"{$search_tag|escape:'html':'UTF-8'}"
        {else}
            {l s='Please enter a search keyword'}
        {/if}
    </p>
{else}
    {if isset($instant_search) && $instant_search}
        <p class="alert alert-info">
            {if $nbProducts == 1}{l s='%d result has been found.' sprintf=$nbProducts|intval}{else}{l s='%d results have been found.' sprintf=$nbProducts|intval}{/if}
        </p>
    {/if}
    <div class="content_sortPagiBar clearfix">
        <div class="sortPagiBar clearfix">
            <div class="pull-left">
                {include file="$tpl_dir./product-sort.tpl"}
            </div>
            <div class="pull-right">
                {include file="./product-compare.tpl"}
            </div>
        </div>
    </div>

    {include file="$tpl_dir./product-list.tpl" products=$search_products}

    <div class="content_sortPagiBar">
        <div class="bottom-pagination-content clearfix">
            {if !isset($instant_search) || (isset($instant_search) && !$instant_search)}
                {include file="$tpl_dir./pagination.tpl" paginationId='bottom' no_follow=1}
            {/if}
        </div>
    </div>
{/if}

{if isset($blogPosts) && $blogPosts}
    <div class="simpleblog-home-posts">
        <h1 class="page-heading">Найдено в блогах</h1>
        {foreach from=$blogPosts item=post}
            <div class="simpleblog-post" itemscope="itemscope" itemtype="http://schema.org/BlogPosting">
                <div class="post-thumbnail" itemscope itemtype="http://schema.org/ImageObject">
                    <a href="{$post.url|escape:'html':'UTF-8'}" itemprop="contentUrl">
                        <img src="{$post.banner_wide|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" class="img-responsive" itemprop="thumbnailUrl"/>
                    </a>
                </div><!-- .post-thumbnail -->

                <div class="post-data">
                    <div class="post-title">
                        <h2 itemprop="headline">
                            <a href="{$post.url|escape:'html':'UTF-8'}">
                                {$post.title|escape:'html':'UTF-8'}
                            </a>
                        </h2>
                        <div class="post-date">
                            <time itemprop="datePublished" datetime="{$post.date_add|date_format:'c'}">
                                {$post.date_add|russian_month_date:Configuration::get('PH_BLOG_DATEFORMAT')}
                            </time>
                        </div>
                    </div><!-- .post-title -->

                    <div class="post-content" itemprop="text">
                        {*{$post.short_content|strip_tags:'UTF-8'|trim|truncate:450:'...'}*}
                        {$post.content|strip_tags:'UTF-8'|trim|truncate:350:'...'}
                    </div><!-- .post-content -->

                    {if Configuration::get('PH_BLOG_DISPLAY_MORE')}
                        <div class="post-read-more">
                            <a href="{$post.url|escape:'html':'UTF-8'}" class="button_large">
                                {l s='Подробнее' mod='ph_simpleblog'}
                            </a>
                        </div><!-- .post-read-more -->
                    {/if}

                </div>
            </div>
        {/foreach}
    </div>
{/if}
