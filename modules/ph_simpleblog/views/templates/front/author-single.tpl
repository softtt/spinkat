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
{assign var='post_type' value='post'}

{capture name=path}
    <a href="{$link->getModuleLink('ph_simpleblog', 'authorslist')|escape:'html':'UTF-8'}" title="{l s='Back to authors page' mod='ph_simpleblog'}">
        {l s='Authors' mod='ph_simpleblog'}
    </a>
    <span class="navigation-pipe">
        {$navigationPipe|escape:'html':'UTF-8'}
    </span>

    {$author->name|escape:'html':'UTF-8'}
{/capture}

{if !$is_16}
    {include file="$tpl_dir./breadcrumb.tpl"}
{/if}

<div itemscope="itemscope" itemtype="http://schema.org/Person">
    <div class="ph_simpleblog simpleblog-single {if !empty($author->image)}with-cover{else}without-cover{/if} simpleblog-authorsingle-{$author->id_simpleblog_author|intval}">
        <h1 class="page-heading" itemprop="name">
            {$author->name|escape:'html':'UTF-8'}
        </h1>

        <div class="block_shadow block-author-info">
            <div class="post-featured-image" itemscope itemtype="http://schema.org/ImageObject" itemprop="image">
                {if $author->image}
                    <a href="#" title="{$author->name|escape:'html':'UTF-8'}" class="fancybox" itemprop="contentUrl">
                        <img src="{$author->image|escape:'html':'UTF-8'}" alt="{$author->name|escape:'html':'UTF-8'}" class="img-responsive" itemprop="thumbnailUrl" />
                    </a>
                {/if}
            </div><!-- .post-featured-image -->

            <div class="author-data">
                <div class="post-content" itemprop="description">
                    {$author->description}
                </div><!-- .post-content -->

                {if isset($categories) && count($categories)}
                    <div class="author-related-posts">
                    {foreach from=$categories item=category}
                        {if isset($category['posts']) && count($category['posts'])}
                            <div class="author-category">
                                <h3>{$category.name|escape:'html':'UTF-8'}</h3>
                                <div>
                                    <ul class="author-category-posts">
                                        {foreach from=$category['posts'] item=post}
                                            <li>
                                                <a href="{$post.url|escape:'html':'UTF-8'}" title="{l s='Permalink to' mod='ph_simpleblog'} {$post.name|escape:'html':'UTF-8'}">
                                                    {$post.title|escape:'html':'UTF-8'}
                                                </a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                    </div>
                {/if}
            </div>
        </div>
    </div><!-- .ph_simpleblog -->
</div><!-- schema -->

<script>
$(function() {
    $('body').addClass('simpleblog simpleblog-authorsingle');
});
</script>
