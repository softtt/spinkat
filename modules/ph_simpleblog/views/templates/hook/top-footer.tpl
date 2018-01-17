<!-- MODULE Block footer -->
{if (isset($categories) && count($categories))}
    <div class="block_simpleblog" id="block_simpleblog_footer">
        <ul>
        {foreach from=$categories item=category}
            <li class="item">
                <a href="{$category['url']|escape:'html':'UTF-8'}" title="{l s='Link to' mod='ph_simpleblog'} {$category['name']|escape:'html':'UTF-8'}">{$category['name']|escape:'html':'UTF-8'}</a>
            </li>
        {/foreach}
        </ul>
    </div>
{/if}
<!-- /MODULE Block footer -->
