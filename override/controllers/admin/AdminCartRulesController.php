<?php

class AdminCartRulesController extends AdminCartRulesControllerCore
{
    public function __construct()
    {
        $this->fieldImageSettings = array(
            'name' => 'logo',
            'dir' => 'cr'
        );

        parent::__construct();
    }
    public function renderForm()
    {
        $current_object = $this->loadObject(true);

        $image = _PS_CART_RULE_IMG_DIR_.$current_object->id.'.jpg';
        $image_url = ImageManager::thumbnail($image, $this->table.'_'.(int)$current_object->id.'.'.$this->imageType, 350,
            $this->imageType, true, true);

        $this->context->smarty->assign(
            array(
                'image_url' => $image_url,
            )
        );

        return parent::renderForm();
    }
}
