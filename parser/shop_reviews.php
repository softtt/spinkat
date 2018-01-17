<?php
    include('lib.php');
    include_once('../modules/users_reviews/models/ShopReview.php');

    $shop_reviews = $pdo->query('SELECT * FROM `users_reviews` WHERE true', PDO::FETCH_ASSOC);

    foreach ($shop_reviews as $shop_review) {

		$new_shop_review = new ShopReview();
		$new_shop_review->customer_name = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($shop_review['name']))));
		$new_shop_review->email = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($shop_review['email']))));
		$new_shop_review->text = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($shop_review['text']))));
		$new_shop_review->grade = 5;
		$new_shop_review->active = $shop_review['visible'];

		$new_shop_review->add();

	}
    
    echo "Экспорт Отзывов о сайте завершён!<br><a href='/parser'>Назад</a>";

?>	