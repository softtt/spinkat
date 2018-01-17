<?php

class BlogSearch
{
    public function __construct()
    {
        // Connect and initiate phpmorphy
        require_once(_PS_MODULE_DIR_.'/ph_simpleblog_search/vendor/phpmorphy/src/common.php');
        $dir = _PS_MODULE_DIR_.'/ph_simpleblog_search/vendor/phpmorphy/dicts';
        $lang = 'ru_RU';
        $opts = ['storage' => PHPMORPHY_STORAGE_FILE];
        try {
            $this->morphy = new phpMorphy($dir, $lang, $opts);
        } catch(phpMorphy_Exception $e) {
            return null;
        }
    }



    public function initialize($query)
    {

        require_once _PS_MODULE_DIR_.'ph_simpleblog/models/SimpleBlogPost.php';

        $page=1;


        $posts = $this->getResultPost($query,8,$page);
        $pagination = $posts[1];
        $total_posts = $posts[2];
        $posts = $posts[0];

        return $posts;
    }


    /**
     * Generates search phrase.
     *
     * Generates search phrase with pseudoroots for given request with phpmorphy and returns generated request.
     *
     * @param string $query Requestred search query.
     *
     * @return string Generated search string.
     */
    public function genSearchWords($query)
    {
        preg_match_all('/([a-zа-яё]+)/ui', mb_strtoupper($query, "UTF-8"), $search_words);
        $words = $this->morphy->getPseudoRoot($search_words[1], $type = 'IGNORE_PREDICT');
        $s_words = [];

        foreach ($words as $k => $w) {
            if (!$w) {
                $w[0] = $k;
            }
            if (mb_strlen($w[0], "UTF-8") > 2) {
                $s_words[] = $w[0];
            }
        }

        $request = implode('* ', $s_words) . '*';
        return $request;
    }

    /**
     * Makes search for given query and parameters.
     *
     * Extracts results matching with given query and for given quantity search page number.
     *
     * @see SearchController::genSearchWords Generate search phrase.
     * @param string $query Search query phrase.
     * @param int $quantity Set search results amount for loading.
     * @param int $page Set search page number.
     *
     * @return array of Blog Posts
     */

    public function getResultPost($query, $quantity=10, $page=1)
    {
        $request = $this->genSearchWords($query);



        $sql = new DbQuery();
        $sql->select('(SELECT COUNT(*) FROM ps_simpleblog_post_lang WHERE MATCH (title, content) AGAINST("'.$request.'" IN BOOLEAN MODE) +
         match(title, content) against ("'.$query.'") ) AS count, id_simpleblog_post');
        $sql->from('simpleblog_post_lang','l');
        $sql->where('MATCH (title, content) AGAINST("'.$request.'" IN BOOLEAN MODE) + match(title, content) against ("'.$query.'")');
        $sql->orderBy('match(l.title) against ("'.$request.'" IN BOOLEAN MODE) desc, match(l.content) against ("'.$request.'" IN BOOLEAN MODE) desc,
         match(l.title,l.content) against ("'.$query.'" IN BOOLEAN MODE)');
        $sql->limit($quantity); // offset for pagination


        $posts = Db::getSearchInstance()->executeS($sql); // get IDs matching query

        if(!count($posts))
        {
            $total_posts = false;
            $blog_posts = false;
        }


        else
        {
            $total_posts = $posts[0]['count'];

            $id_lang = Context::getContext()->language->id;
            $ids = array();

            foreach ($posts as $no=> $val) {
                /// get posts by id
                $ids[] = $val['id_simpleblog_post'];
            }

            $blog_posts = SimpleBlogPost::getPosts($id_lang,$quantity,null,null,true,false,false,null,false,false,null,'in',$ids);
        }


        $blog_posts_pagination[0] = $blog_posts;
        $blog_posts_pagination[1] = false;
        $blog_posts_pagination[2] = $total_posts;

        return $blog_posts_pagination;

    }
}
