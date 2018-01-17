<?php
    include('lib.php');

    $customers = $pdo->query('SELECT * FROM `customers` WHERE true', PDO::FETCH_ASSOC);

    foreach ($customers as $customer) {
    	
    	$new_customer = new CustomerCore();

    	$new_customer->secure_key = md5(uniqid(rand(), true));

    	$new_customer->id_shop_group = 1;
    	$new_customer->id_shop = 1;
    	$new_customer->id_gender = 0;
    	$new_customer->default_group = 3;
    	$new_customer->id_lang = 1;
    	$new_customer->id_risk = 0;

    	$new_customer->lastname = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($customer['lastname']))));
    	$new_customer->firstname = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($customer['firstname']))));
    	$new_customer->email = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($customer['email']))));
    	$new_customer->passwd = Tools::encrypt($customer['password']);
    	$new_customer->last_passwd_gen = date('Y-m-d H:i:s', strtotime('-'.Configuration::get('PS_PASSWD_TIME_FRONT').'minutes'));
    	// $new_customer->id_gender = ;  // absent in old db
    	// $new_customer->birthday = ;  // absent in old db
    	$new_customer->newsletter = 1;
        // $new_customer->ip_registration_newsletter = ; //default = null
        // $new_customer->newsletter_date_add ?? 00000
        $new_customer->optin = $customer['subscribed']; //subscribe
        // $new_customer->website = ; //default = null
        $new_customer->outstanding_allow_amount = 0;
        $new_customer->show_public_prices = 0;
        $new_customer->max_payment_days = 0;
        $new_customer->is_guest = 0;    
        $new_customer->date_add = $customer['created'];

        $new_customer->add();

    }

    echo "Экспорт Юзеров завершён!<br><a href='/parser'>Назад</a>";

?>