{if $telnumber}
    <div id="footer_phones">
        <span>Телефоны:</span>
        {foreach from=$telnumber item=number}
            <span class="shop-phone"><a href="tel:{$number}">{$number}</a></span>
        {/foreach}
    </div>
{/if}
