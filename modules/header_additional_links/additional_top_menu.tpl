{if isset($links) && count($links)}
    <div class="additional-top-links col-md-4" id="additional-top-links">
        <select class="form-control no-print">
            {if isset($default) && $default != ''}
                <option disable>{$default|escape:'html':'UTF-8'}</option>
            {/if}
            {foreach from=$links item=link}
                <option value="{$link['link']|escape:'html':'UTF-8'}">{$link['label']|escape:'html':'UTF-8'}</option>
            {/foreach}
        </select>
    </div>
{/if}
