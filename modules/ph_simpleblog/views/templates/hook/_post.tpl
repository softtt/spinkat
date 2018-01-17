<div class="simpleblog-post col-md-4">
    <div class="post-thumbnail" itemscope itemtype="http://schema.org/ImageObject">
        <a href="{$post.url|escape:'html':'UTF-8'}" title="{l s='Permalink to' mod='ph_simpleblog'} {$post.title|escape:'html':'UTF-8'}" itemprop="contentUrl">
            <img src="{$post.banner_thumb|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" class="img-responsive" itemprop="thumbnailUrl"/>
        </a>
    </div><!-- .post-thumbnail -->

    <div class="post-data">
        <div class="post-title">
            <h2 itemprop="headline">
                <a href="{$post.url|escape:'html':'UTF-8'}" title="{l s='Permalink to' mod='ph_simpleblog'} {$post.title|escape:'html':'UTF-8'}">
                    {$post.title|escape:'html':'UTF-8'}
                </a>
            </h2>
            <div class="post-date">
                <time itemprop="datePublished" datetime="{$post.date_add|date_format:'c'}">
                    {$post.date_add|russian_month_date:Configuration::get('PH_BLOG_DATEFORMAT')}
                </time>
            </div>
        </div><!-- .post-title -->

        <div class="post-content" itemprop="text">
            {$post.short_content|strip_tags:'UTF-8'|trim|truncate:450:'...'}
        </div><!-- .post-content -->

        {if Configuration::get('PH_BLOG_DISPLAY_MORE')}
        <div class="post-read-more">
            <a href="{$post.url|escape:'html':'UTF-8'}" title="{l s='Подробнее' mod='ph_simpleblog'}" class="button_large">
                {l s='Подробнее' mod='ph_simpleblog'}
            </a>
        </div><!-- .post-read-more -->
        {/if}

    </div>
</div>
