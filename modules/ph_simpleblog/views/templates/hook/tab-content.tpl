{if isset($articles) && $articles}
    <div role="tabpanel" class="tab-pane" id="articles">
        <div class="row">
            {foreach from=$articles item=post}
                {include file="./_post.tpl"}
            {/foreach}
        </div>
    </div>
{/if}

{if isset($surveys) && $surveys}
    <div role="tabpanel" class="tab-pane" id="reviews">
        <div class="row">
            {foreach from=$surveys item=post}
                {include file="./_post.tpl"}
            {/foreach}
        </div>
    </div>
{/if}

{if isset($blog) && $blog}
    <div role="tabpanel" class="tab-pane" id="blog">
        <div class="row">
            {foreach from=$blog item=post}
                {include file="./_post.tpl"}
            {/foreach}
        </div>
    </div>
{/if}

