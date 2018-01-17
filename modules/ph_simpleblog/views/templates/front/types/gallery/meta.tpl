<div class="post-additional-info post-meta-info">
	{if Configuration::get('PH_BLOG_DISPLAY_DATE')}
		<span class="post-date">
			<i class="fa fa-calendar"></i> <time itemprop="datePublished" datetime="{$post.date_add|date_format:'c'}">{$post.date_add|date_format:Configuration::get('PH_BLOG_DATEFORMAT')}</time>
		</span>
	{/if}

	{if $is_category eq false && Configuration::get('PH_BLOG_DISPLAY_CATEGORY')}
		<span class="post-category">
			<i class="fa fa-tags"></i> <a href="{$post.category_url}" title="{$post.category|escape:'html':'UTF-8'}" rel="category">{$post.category|escape:'html':'UTF-8'}</a>
		</span>
	{/if}

	{if isset($post.author_name) && !empty($post.author_name)
	 && isset($post.display_author) && $post.display_author}
		<span class="post-author">
			<i class="fa fa-user"></i> <a href="{$post.author_url|escape:'html':'UTF-8'}" itemprop="author" itemscope="itemscope" itemtype="http://schema.org/Person">{$post.author_name|escape:'html':'UTF-8'}</a>
		</span>
	{/if}

	{if isset($post.tags) && $post.tags && isset($post.display_tags) && $post.display_tags}
		<span class="post-tags clear">
			{l s='Tags:' mod='ph_simpleblog'}
			{foreach from=$post.tags key=tag_id item=tag name='tagsLoop'}
				<a href="{SimpleBlogCategory::getLink($post.category_rewrite, null, null, ['tag' => $tag_id])|escape:'html':'UTF-8'}">{$tag|escape:'html':'UTF-8'}</a>{if !$smarty.foreach.tagsLoop.last}, {/if}
			{/foreach}
		</span>
	{/if}
</div><!-- .post-additional-info post-meta-info -->
