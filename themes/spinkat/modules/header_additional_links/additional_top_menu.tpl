{if isset($links) && count($links)}
    <div class="additional-top-links menu-section">
        <div id="additional-top-links">
            <select class="form-control not_uniform no-print">
                {if isset($default) && $default != ''}
                    <option>{$default|escape:'html':'UTF-8'}</option>
                {/if}
                {foreach from=$links item=link}
                    <option value="{$link['link']|escape:'html':'UTF-8'}">{$link['label']|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <script type="text/javascript">
            $(function() {
                $('.additional-top-links select').uniform({
                    selectAutoWidth: false,
                    selectClass: 'additional-top-links-selector'
                });
            });
        </script>
    </div>
{/if}
