{if isset($articles) && $articles}
    <li role="presentation" class="articles"><a href="#articles" aria-controls="articles" role="tab" data-toggle="tab">Статьи</a></li>
{/if}

{if isset($surveys) && $surveys}
    <li role="presentation" class="reviews"><a href="#reviews" aria-controls="reviews" role="tab" data-toggle="tab">Обзоры</a></li>
{/if}

{if isset($blog) && $blog}
    <li role="presentation" class="blog"><a href="#blog" aria-controls="blog" role="tab" data-toggle="tab">Записи в блогах</a></li>
{/if}
