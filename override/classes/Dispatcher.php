<?php

class Dispatcher extends DispatcherCore
{
    protected function __construct()
    {
        $this->default_routes['series_rule'] = array(
            'controller' =>    'series',
            'rule' =>        '{id_category}-{category:/}{id}-{rewrite}',
            'keywords' => array(
                'id' => array('regexp' => '[0-9]+', 'param' => 'id_series'),
                'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                'id_category' => array('regexp' => '[0-9]+', 'param' => 'id_category'),
                'category' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
            ),
        );

        $this->default_routes['layered_rule']['rule'] = '{id}-{rewrite}/filter{/:selected_filters}';

        $this->default_routes['tags_rule'] = array(
            'controller' =>    'tags',
            'rule' =>        'tags/{id}-{rewrite}',
            'keywords' => array(
                'id' =>            array('regexp' => '[0-9]+', 'param' => 'id_tag'),
                'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
            ),
        );

        parent::__construct();
    }
}
