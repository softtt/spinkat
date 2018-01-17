<?php
    include('lib.php');

    $features = $pdo->query('SELECT * FROM `feature_type` WHERE `parent` = 0 AND true', PDO::FETCH_ASSOC);

    foreach ($features as $feature) {

        $new_feature = new FeatureCore();

        // getting highter position of features
        $sql = 'SELECT MAX(position), position FROM '._DB_PREFIX_.'feature';
        $higher_position = Db::getInstance()->getValue($sql);

        $new_feature->position = $higher_position + 1;

        //lang fields
        foreach (Language::getLanguages(true) as $lang){
            $new_feature->name[$lang['id_lang']] = mysql_escape_string($feature['header']);
        }

        $new_feature->add();

        $feature_values = $pdo->query('SELECT `header` FROM `feature_type` WHERE `parent` = '.$feature['id'].' AND true', PDO::FETCH_ASSOC);

        foreach ($feature_values as $feature_value) {

            $new_feature_value = new FeatureValueCore();

            $new_feature_value->id_feature = $new_feature->id;
            $new_feature_value->custom = 0;

            //lang fields
            foreach (Language::getLanguages(true) as $lang){
                $new_feature_value->value[$lang['id_lang']] = mysql_escape_string($feature_value['header']);
            }

            $new_feature_value->add();
        }

        if ($feature['mult'] == 0 && $feature['parent'] == 0) {

            $non_list_feature_values = $pdo->query('SELECT `header` FROM `feature_bind` WHERE `feature_id` = '.$feature['id'].' AND true', PDO::FETCH_ASSOC);    
           
            foreach ($non_list_feature_values as $non_list_feature_value) {

                // ckeking for entries
                $sql = 'SELECT COUNT(value) FROM '._DB_PREFIX_.'feature_value_lang WHERE value = '.$non_list_feature_value['header'];
                $is_isset = Db::getInstance()->getValue($sql);


                if ((bool)$is_isset == false){

                    $new_feature_value = new FeatureValueCore();

                    $new_feature_value->id_feature = $new_feature->id;
                    $new_feature_value->custom = 0;

                    //lang fields
                    foreach (Language::getLanguages(true) as $lang){
                        $new_feature_value->value[$lang['id_lang']] = mysql_escape_string($non_list_feature_value['header']);
                    }

                    $new_feature_value->add();
                }
            }
        }
    }

echo 'Экспорт Характеристик завершён!<br><a href="/parser">Назад</a>';

?>