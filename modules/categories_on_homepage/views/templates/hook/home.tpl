<!-- Categories on homepage -->
{if isset($categories) && count($categories)}
    <div id="categories-on-homepage" class="col-xs-12 col-md-12">
        {foreach from=$categories item=category}
            <div class="col-md-2 homepage-category">
                <a href="{$link->getCategoryLink($category['id_category'], $category['link_rewrite'])|escape:'html':'UTF-8'}" class="category-img"><img class="img-responsive" src="{$category['image']|escape:'html':'UTF-8'}" alt="{$category['name']|escape:'html':'UTF-8'}"></a>
                <a href="{$link->getCategoryLink($category['id_category'], $category['link_rewrite'])|escape:'html':'UTF-8'}" class="category-title">{$category['name']|escape:'html':'UTF-8'}</a>
            </div>
        {/foreach}
    </div>
    <div class="clearfix"></div>
{/if}
<!-- / Categories on homepage -->
