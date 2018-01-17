{include file="$tpl_dir./errors.tpl"}

{if !isset($errors) OR !sizeof($errors)}
    <h1 class="page-heading product-listing">
        {if isset($tag->title_for_seo_h1) && $tag->title_for_seo_h1}
            {$tag->title_for_seo_h1|escape:'html':'UTF-8'}
        {else}
            Тег&nbsp;{$tag->name|escape:'html':'UTF-8'}
        {/if}
    </h1>

    {if isset($products) && $products}

        {include file="./product-list.tpl" products=$products}

        <div class="content_sortPagiBar">
            <div class="bottom-pagination-content clearfix">
                {*{include file="./product-compare.tpl"}*}
                {include file="./pagination.tpl" no_follow=1 paginationId='bottom'}
            </div>
        </div>
    {else}
        <p class="alert alert-warning">Нет товаров.</p>
    {/if}

    {if !empty($tag->description)}
        <div class="description_box rte">
            <div class="hide_desc">
                {$tag->description}
            </div>
        </div>
    {/if}
{/if}


