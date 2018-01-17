<?php

class IndexController extends IndexControllerCore
{
    public function initContent()
    {
        $this->context->smarty->assign(array(
            'is_homepage' => true
        ));
        parent::initContent();
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addjqueryPlugin('trunk8');
    }
}
