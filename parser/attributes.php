<?php
    include('lib.php');

    $attribute_groups = $pdo->query('SELECT * FROM `feature_type` WHERE `parent` = 0 AND true', PDO::FETCH_ASSOC);

    foreach ($attribute_groups as $attribute_group) {

        // getting highter position of attribute_groups
        $sql = 'SELECT MAX(position), position FROM '._DB_PREFIX_.'attribute_group';
        $higher_position = Db::getInstance()->getValue($sql);


        $new_attribute_group = new AttributeGroupCore();

        $new_attribute_group->is_color_group = 0;
        $new_attribute_group->group_type = 'radio';
        $new_attribute_group->position = $higher_position + 1;

        //lang fields
        foreach (Language::getLanguages(true) as $lang){
            $new_attribute_group->name[$lang['id_lang']] = $attribute_group['header'];
            $new_attribute_group->public_name[$lang['id_lang']] = $attribute_group['header'];
        }

        $new_attribute_group->add();
    }


    $attributes = $pdo->query('SELECT * FROM `feature_type` WHERE `parent` > 0 AND true', PDO::FETCH_ASSOC);

    foreach ($attributes as $attribute) {

        // getting highter position of attributes
        $sql = 'SELECT MAX(position), position FROM '._DB_PREFIX_.'attribute';
        $higher_position = Db::getInstance()->getValue($sql);

        // getting attribute_group_id - means parents is of attribute 
        $attribute_parents_name = $pdo->query('SELECT `header` FROM `feature_type` WHERE `id` = '.$attribute["parent"].' AND true', PDO::FETCH_ASSOC);
        foreach ($attribute_parents_name as $name) {
            $sql = 'SELECT id_attribute_group FROM '._DB_PREFIX_.'attribute_group_lang WHERE name = "'.$name['header'].'"';
            $parent_id = Db::getInstance()->getValue($sql);
       }       

        $new_attribute = new AttributeCore();

        $new_attribute->id_attribute_group = $parent_id;
        $new_attribute->color = NULL; //as default
        $new_attribute->position = $higher_position + 1;

        //lang fields
        foreach (Language::getLanguages(true) as $lang){
            $new_attribute->name[$lang['id_lang']] = $attribute['header'];
        }

        $new_attribute->add();
    }


    $id_non_list_attributes = $pdo->query('SELECT `id`, `header` FROM `feature_type` WHERE `parent` = 0 AND `mult` = 0 AND true', PDO::FETCH_ASSOC);

    foreach ($id_non_list_attributes as $id_non_list_attribute) {
            
        $value_of_non_list_attr = $pdo->query('SELECT `header` FROM `feature_bind` WHERE `feature_id` ='.$id_non_list_attribute['id'].' AND true', PDO::FETCH_ASSOC);

        foreach ($value_of_non_list_attr as $value) {

            // getting id of paren attribute in new db
            $sql = 'SELECT id_attribute_group FROM '._DB_PREFIX_.'attribute_group_lang WHERE name = "'.$id_non_list_attribute["header"].'"';
            $parent_id = Db::getInstance()->getValue($sql);


            $sql = 'SELECT COUNT(name) FROM ps_attribute_lang WHERE name = "'.$value['header'].'"';
            $is_isset = Db::getInstance()->getValue($sql);

            if ($is_isset == 0){

                // getting highter position of attributes
                $sql = 'SELECT MAX(position), position FROM '._DB_PREFIX_.'attribute';
                $higher_position = Db::getInstance()->getValue($sql);

                $new_attribute = new AttributeCore();

                $new_attribute->id_attribute_group = $parent_id;
                $new_attribute->color = NULL; //as default
                $new_attribute->position = $higher_position + 1;

                //lang fields
                foreach (Language::getLanguages(true) as $lang){
                    $new_attribute->name[$lang['id_lang']] = $value['header'];
                }

                $new_attribute->add();
            }
        }
    }

    echo "Экспорт Атрибутов завершён!<br><a href='/parser'>Назад</a>";