{if (isset($categories) && count($categories))}
    <!-- SimpleBlog home -->
    <div id="simpleblog-home">
        {foreach from=$categories item=category}
            {if (isset($category['posts']) && count($category['posts']))}
                <div class="simpleblog-home-category">
                    <div class="home-title">
                        <h2 class="title">{$category['homepage_title']|escape:'html':'UTF-8'}
                            {*<a href="{$category['url']|escape:'html':'UTF-8'}">{$shop_name|escape:'html':'UTF-8'}</a>*}
                        </h2>
                        <span class="line"></span>
                        <span class="page-link">
                            <a href="{$category['url']|escape:'html':'UTF-8'}">{l s='All' mod='ph_simpleblog'} {$category['name']|escape:'html':'UTF-8'}</a>
                        </span>
                    </div>

                    <div class="simpleblog-home-posts">
                        {foreach from=$category['posts'] item=post}
                            <div class="simpleblog-post" itemscope="itemscope" itemtype="http://schema.org/BlogPosting">
                                <div class="post-thumbnail" itemscope itemtype="http://schema.org/ImageObject">
                                    <a href="{$post.url|escape:'html':'UTF-8'}" itemprop="contentUrl">
                                        <img src="{$post.banner_wide|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" class="img-responsive" itemprop="thumbnailUrl"/>
                                    </a>
                                </div><!-- .post-thumbnail -->

                                <div class="post-data">
                                    <div class="post-title">
                                        <h2 itemprop="headline">
                                            <a href="{$post.url|escape:'html':'UTF-8'}">
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
                                        {*{$post.short_content|strip_tags:'UTF-8'|trim|truncate:450:'...'}*}
                                        {$post.content|strip_tags:'UTF-8'|trim|truncate:350:'...'}
                                    </div><!-- .post-content -->

                                    {if Configuration::get('PH_BLOG_DISPLAY_MORE')}
                                    <div class="post-read-more">
                                        <a href="{$post.url|escape:'html':'UTF-8'}" class="button_large">
                                            {l s='Read more' mod='ph_simpleblog'}
                                        </a>
                                    </div><!-- .post-read-more -->
                                    {/if}

                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            {/if}
        {/foreach}
    </div>
    <!-- / SimpleBlog home -->
{/if}
