{include file="$tpl_dir./errors.tpl"}
<h1 class="page-heading product-listing"><span class="cat-name">{$series->getFullName()|escape:'html':'UTF-8'}</span></h1>

<div id="blocklayered_copy_mobile" class="display-on-mobile"></div>

{if $models}
    {include file="./product-list.tpl" products=$models}
    <div class="content_sortPagiBar">
        <div class="bottom-pagination-content clearfix">
            {include file="./pagination.tpl" paginationId='bottom'}
        </div>
    </div>
{/if}

