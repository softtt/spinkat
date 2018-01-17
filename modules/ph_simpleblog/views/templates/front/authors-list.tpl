{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if Configuration::get('PH_BLOG_DISPLAY_BREADCRUMBS')}
    {capture name=path}
        {l s='Authors' mod='ph_simpleblog'}
    {/capture}
    {if !$is_16}
        {include file="$tpl_dir./breadcrumb.tpl"}
    {/if}
{/if}

<div class="ph_simpleblog simpleblog-authorslist">
    <h1 class="page-heading">{$authorsMainTitle|escape:'html':'UTF-8'}</h1>

    {if isset($authors) && count($authors)}
        <div class="simpleblog-posts">
            {foreach from=$authors item=post}

                {assign var='post_type' value='post'}

                <div class="simpleblog-post-item block_shadow simpleblog-post-type-{$post_type|escape:'html':'UTF-8'}
                {if $blogLayout eq 'grid' AND $columns eq '3'}
                    col-md-4 col-sm-6 col-xs-12 col-ms-12 {cycle values="first-in-line,second-in-line,last-in-line"}
                {elseif $blogLayout eq 'grid' AND $columns eq '4'}
                    col-md-3 col-sm-6 col-xs-12 col-ms-12 {cycle values="first-in-line,second-in-line,third-in-line,last-in-line"}
                {elseif $blogLayout eq 'grid' AND $columns eq '2'}
                    col-md-6 col-sm-6 col-xs-12 col-ms-12 {cycle values="first-in-line,last-in-line"}
                {else}
                col-md-12
                {/if}">

                    <div class="post-item" itemscope="itemscope" itemtype="http://schema.org/Person">
                        {if isset($post.banner) && Configuration::get('PH_BLOG_DISPLAY_THUMBNAIL')}
                            <div class="post-thumbnail" itemscope itemtype="http://schema.org/ImageObject" itemprop="image">
                                <a href="{$post.url|escape:'html':'UTF-8'}" title="{l s='Permalink to' mod='ph_simpleblog'} {$post.name|escape:'html':'UTF-8'}" itemprop="contentUrl">
                                    {if $blogLayout eq 'full'}
                                        <img src="{$post.banner_wide|escape:'html':'UTF-8'}" alt="{$post.name|escape:'html':'UTF-8'}" class="img-responsive" itemprop="thumbnailUrl" />
                                    {else}
                                        <img src="{$post.banner_thumb|escape:'html':'UTF-8'}" alt="{$post.name|escape:'html':'UTF-8'}" class="img-responsive" itemprop="thumbnailUrl"/>
                                    {/if}
                                </a>
                            </div><!-- .post-thumbnail -->
                        {/if}

                        <div class="post-content-block">
                            {if $post_type != 'post' && file_exists("$tpl_path./types/$post_type/title.tpl")}
                                {include file="./types/$post_type/title.tpl"}
                            {else}
                                <div class="post-title">
                                    <h2 itemprop="name">
                                        <a href="{$post.url|escape:'html':'UTF-8'}" title="{l s='Permalink to' mod='ph_simpleblog'} {$post.name|escape:'html':'UTF-8'}">
                                            {$post.name|escape:'html':'UTF-8'}
                                        </a>
                                    </h2>
                                </div><!-- .post-title -->
                            {/if}

                            {if Configuration::get('PH_BLOG_DISPLAY_DESCRIPTION')}
                                <div class="post-content" itemprop="description">
                                    {$post.description_short|strip_tags:'UTF-8'}
                                </div><!-- .post-content -->
                            {/if}
                        </div>

                    </div><!-- .post-item -->
                </div><!-- .simpleblog-post-item -->

            {/foreach}
        </div><!-- .row -->
    {else}
        <p class="warning alert alert-warning">{l s='There are no authors' mod='ph_simpleblog'}</p>
    {/if}
</div><!-- .ph_simpleblog -->
<script>
$(window).load(function() {
    $('body').addClass('simpleblog simpleblog-authorslist');
});
</script>
