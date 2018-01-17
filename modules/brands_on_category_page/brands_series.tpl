{if ($manufacturers && count($manufacturers))}
<div id="brands-series-block" class="row">
    {foreach from=$manufacturers item=manufacturer}
        <div class="col-xs-3 brand_wrapper">
            <div class="block_shadow brand">
                <a href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'html':'UTF-8'}">
                    {if $manufacturer.image}
                        <img src="{$img_manu_dir}{$manufacturer.image|escape:'html':'UTF-8'}.jpg" alt="{$manufacturer.name|escape:'html':'UTF-8'}" />
                    {else}
                        {$manufacturer.name}
                    {/if}
                </a>
            </div>
            {if isset($manufacturer['series']) && $manufacturer['series']}
                <div class="brand-series">
                    <ul class="block_shadow">
                        {foreach from=$manufacturer['series'] item=s}
                            <li>
                                <a href="{$s['link']|escape:'html':'UTF-8'}">{$s['series_name']|escape:'html':'UTF-8'}</a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
        </div>
    {/foreach}
    <div class="line clearfix"></div>
</div>
{/if}
