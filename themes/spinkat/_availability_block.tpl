{if ($allow_oosp || $quantity > 0)}
    <span class="availability {if $quantity <= 20} product-available-out-of-stock{else} product-available-in-stock{/if}">
        <span>
            {if $quantity <= 20}
                {if $allow_oosp || $quantity > 0}
                    {l s='Precise availability of product'}
                {else}
                    {l s='Out of stock'}
                {/if}
            {else}
                {l s='In Stock'}
            {/if}
        </span>
    </span>
{else}
    <span class="availability product-out-of-stock">
        <span class="product-out-of-stock">
            {l s='Out of stock'}
        </span>
    </span>
{/if}
