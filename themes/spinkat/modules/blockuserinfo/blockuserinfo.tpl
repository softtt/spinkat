<div class="block-user-info-nav menu-section">
    <!-- Block user information module NAV  -->
    {if $logged}
    <div class="header_user_info">
        <a href="{$link->getPageLink('my-account', true)|escape:'html'}" title="{l s='View my customer account' mod='blockuserinfo'}" class="account" rel="nofollow"><span>{$cookie->customer_firstname}</span></a>
    </div>
    {/if}

    <div class="header_user_info">
        {if $logged}
            <a class="logout" href="{$link->getPageLink('index', true, NULL, "mylogout")|escape:'html'}" rel="nofollow" title="{l s='Log me out' mod='blockuserinfo'}">{l s='Sign out' mod='blockuserinfo'}</a>
        {else}
            <a class="login" href="{$link->getPageLink('my-account', true)|escape:'html'}" rel="nofollow" title="{l s='Log in to your customer account' mod='blockuserinfo'}">{l s='Sign up' mod='blockuserinfo'}</a>
        {/if}
    </div>

    {if !$logged}
        <div class="header_user_info">
            <span class="link-icon user"></span>
            <a class="login" href="{$link->getPageLink('my-account', true)|escape:'html'}" rel="nofollow" title="{l s='Log in to your customer account' mod='blockuserinfo'}">{l s='Sign in' mod='blockuserinfo'}</a>
        </div>
    {/if}
    <!-- /Block user information module NAV -->
</div>
