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
{assign var='post_type' value=$post->post_type}
{capture name=path}
{*
    <a href="{$link->getModuleLink('ph_simpleblog', 'list')|escape:'html':'UTF-8'}" title="{l s='Back to blog homepage' mod='ph_simpleblog'}">
        {l s='Blog' mod='ph_simpleblog'}
    </a>
    <span class="navigation-pipe">
        {$navigationPipe|escape:'html':'UTF-8'}
    </span>
    {if isset($post->parent_category) && $post->parent_category != ''}
        <a href="{$link->getModuleLink('ph_simpleblog', 'category', ['sb_category' => $post->parent_category->link_rewrite])|escape:'html':'UTF-8'}">{$post->parent_category->name|escape:'html':'UTF-8'}</a>
        <span class="navigation-pipe">
            {$navigationPipe|escape:'html':'UTF-8'}
        </span>
    {/if}
*}
    <a href="{$post->category_url|escape:'html':'UTF-8'}">{$post->category|escape:'html':'UTF-8'}</a>
    <span class="navigation-pipe">
        {$navigationPipe|escape:'html':'UTF-8'}
    </span>

    {$post->title|escape:'html':'UTF-8'}
{/capture}

{if !$is_16}
    {include file="$tpl_dir./breadcrumb.tpl"}
{/if}

{include file="$tpl_dir./errors.tpl"}

{if isset($smarty.get.confirmation)}
    <div class="success alert alert-success">
    {if $smarty.get.confirmation == 1}
        {l s='Your comment was sucessfully added.' mod='ph_simpleblog'}
    {else}
        {l s='Your comment was sucessfully added but it will be visible after moderator approval.' mod='ph_simpleblog'}
    {/if}
    </div><!-- .success alert alert-success -->
{/if}

<div itemscope="itemscope" itemtype="http://schema.org/Blog">
    <div class="ph_simpleblog my-post-single simpleblog-single {if !empty($post->featured_image)}with-cover{else}without-cover{/if} simpleblog-single-{$post->id_simpleblog_post|intval}" itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost">

        <div class="block_shadow post-content-block">

            <h1 itemprop="headline">
                {$post->title|escape:'html':'UTF-8'}
            </h1>

            {*
            <div class="post-featured-image" itemscope itemtype="http://schema.org/ImageObject">
                {if $post->featured_image}
                    <a href="{$post->featured_image|escape:'html':'UTF-8'}" title="{$post->title|escape:'html':'UTF-8'}" class="fancybox" itemprop="contentUrl">
                        <img src="{$post->featured_image|escape:'html':'UTF-8'}" alt="{$post->title|escape:'html':'UTF-8'}" class="img-responsive" itemprop="thumbnailUrl" />
                    </a>
                {/if}
            </div><!-- .post-featured-image -->
            *}

            <div class="post-content rte" itemprop="text">
                {$post->content}
            </div><!-- .post-content -->

            <div class="post-meta-info row">

                {if $post->display_author && isset($post->author_thumb) && $post->author_thumb}
                    <div class="experts-foto-link">
                        <a href="{$post->simpleblog_author_url|escape:'html':'UTF-8'}">
                            <img src="{$post->author_thumb}" class="experts-foto" alt="{$post->simpleblog_author_url|escape:'html':'UTF-8'}">
                        </a>
                    </div>
                {/if}

                <div class="post-data">
                    {if isset($post->simpleblog_author) && !empty($post->simpleblog_author)
                     && isset($post->display_author) && $post->display_author}
                        <div class="post-author">
                            <span>Автор: </span><a href="{$post->simpleblog_author_url|escape:'html':'UTF-8'}" itemprop="author" itemscope="itemscope" itemtype="http://schema.org/Person">{$post->simpleblog_author->name|escape:'html':'UTF-8'}</a>
                        </div>
                    {/if}
                    {if Configuration::get('PH_BLOG_DISPLAY_DATE') && $post_type == 'post'}
                        <div class="post-date">
                            <span>Опубликовано: </span> <span><time itemprop="datePublished" datetime="{$post->date_add|date_format:'c'}">{$post->date_add|russian_month_date:Configuration::get('PH_BLOG_DATEFORMAT')}</time></span>
                        </div>
                    {/if}

                    <div class="post-date">
                        <div data-background-alpha="0.0" data-buttons-color="#FFFFFF" data-counter-background-color="#ffffff" data-share-counter-size="12" data-top-button="false" data-share-counter-type="disable" data-share-style="1" data-mode="share" data-like-text-enable="false" data-mobile-view="true" data-icon-color="#ffffff" data-orientation="horizontal" data-text-color="#000000" data-share-shape="round-rectangle" data-sn-ids="vk.ok.fb.mr.tw." data-share-size="30" data-background-color="#ffffff" data-preview-mobile="false" data-mobile-sn-ids="fb.vk.tw.wh.ok.vb." data-pid="1549621" data-counter-background-alpha="1.0" data-following-enable="false" data-exclude-show-more="true" data-selection-enable="true" class="uptolike-buttons" ></div>
                    </div>

                    {*
                    {if Configuration::get('PH_BLOG_DISPLAY_CATEGORY')}
                        <span class="post-category">
                            <i class="fa fa-tags"></i> <a href="{$post->category_url|escape:'html':'UTF-8'}" title="{$post->category|escape:'html':'UTF-8'}">{$post->category|escape:'html':'UTF-8'}</a>
                        </span>
                    {/if}
                    *}

                    {if $post->tags && isset($post->display_tags) && $post->display_tags && isset($post->tags_list)}
                        <div class="post-tags clear">
                            <span>Метки</span>
                            {foreach from=$post->tags_list key=tag_id item=tag name='tagsLoop'}
                                <a href="{SimpleBlogCategory::getLink($post->category_rewrite, null, null, ['tag' => $tag_id])|escape:'html':'UTF-8'}">{$tag|escape:'html':'UTF-8'}</a>{if !$smarty.foreach.tagsLoop.last}, {/if}
                            {/foreach}
                        </div>
                    {/if}

                    {if Configuration::get('PH_BLOG_DISPLAY_LIKES')}
                        <span class="post-likes">
                            <a href="#" data-guest="{$cookie->id_guest|intval}" data-post="{$post->id_simpleblog_post|intval}" class="simpleblog-like-button">
                                <i class="fa fa-heart"></i>
                                <span>{$post->likes|intval}</span> {l s='likes'  mod='ph_simpleblog'}
                            </a>
                        </span>
                    {/if}

                    {if Configuration::get('PH_BLOG_DISPLAY_VIEWS')}
                        <span class="post-views">
                            <i class="fa fa-eye"></i> {$post->views|escape:'html':'UTF-8'} {l s='views'  mod='ph_simpleblog'}
                        </span>
                    {/if}

                    {*
                    {if $allow_comments eq true && Configuration::get('PH_BLOG_NATIVE_COMMENTS')}
                    <span class="post-comments">
                        <i class="fa fa-comments"></i> {$post->comments|escape:'html':'UTF-8'} {l s='comments'  mod='ph_simpleblog'}
                    </span>
                    {/if}
                    *}
                </div>
            </div><!-- .post-meta-info -->

            {if $post_type == 'gallery' && sizeof($post->gallery)}
            <div class="post-gallery">
                {foreach $post->gallery as $image}
                <a rel="post-gallery-{$post->id_simpleblog_post|intval}" class="fancybox col-xs-3" href="{$gallery_dir|escape:'html':'UTF-8'}{$image.image|escape:'html':'UTF-8'}.jpg"><img src="{$gallery_dir|escape:'html':'UTF-8'}{$image.image|escape:'html':'UTF-8'}-thumb.jpg" class="img-responsive" /></a>
                {/foreach}
            </div><!-- .post-gallery -->
            {elseif $post_type == 'video'}
            <div class="post-video" itemprop="video">
                {$post->video_code}
            </div><!-- .post-video -->
            {/if}

        </div>

        {*
        {if Configuration::get('PH_BLOG_DISPLAY_RELATED') && $related_products}
            {include file="./related-products.tpl"}
        {/if}
        *}

        <div id="displayPrestaHomeBlogAfterPostContent">
            {hook h='displayPrestaHomeBlogAfterPostContent'}
        </div><!-- #displayPrestaHomeBlogAfterPostContent -->

        {* Native comments *}
        {if $allow_comments eq true && Configuration::get('PH_BLOG_NATIVE_COMMENTS')}
            {include file="./comments/layout.tpl"}
        {/if}

        {* Facebook comments *}
        {*
        {if $allow_comments eq true && !Configuration::get('PH_BLOG_NATIVE_COMMENTS')}
            {include file="./comments/facebook.tpl"}
        {/if}
        *}

    </div><!-- .ph_simpleblog -->
</div><!-- schema -->

{if Configuration::get('PH_BLOG_FB_INIT')}
<script>
var lang_iso = '{$lang_iso}_{$lang_iso|@strtoupper}';
{literal}(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/"+lang_iso+"/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
{/literal}
</script>
{/if}
<script>
$(function() {
    $('body').addClass('simpleblog simpleblog-single');
});
</script>

<script type="text/javascript">(function(w,doc) {
if (!w.__utlWdgt ) {
    w.__utlWdgt = true;
    var d = doc, s = d.createElement('script'), g = 'getElementsByTagName';
    s.type = 'text/javascript'; s.charset='UTF-8'; s.async = true;
    s.src = ('https:' == w.location.protocol ? 'https' : 'http')  + '://w.uptolike.com/widgets/v1/uptolike.js';
    var h=d[g]('body')[0];
    h.appendChild(s);
}})(window,document);
</script>
