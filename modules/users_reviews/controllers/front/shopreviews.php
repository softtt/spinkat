<?php

class users_reviewsShopReviewsModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();

        $this->context = Context::getContext();

        include_once $this->module->getLocalPath().'models/ShopReview.php';
    }

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $reviews = ShopReview::getReviews();

        $this->context->smarty->assign(array(
            'reviews' => $reviews,
            'email' => $this->context->cookie->email ? $this->context->cookie->email : '',
            'errors' => $this->errors,
        ));

        if ($this->errors) {
            $this->context->smarty->assign(array(
                'post_name' => Tools::getValue('customer_name', null),
                'post_email' => Tools::getValue('email', null),
                'post_text' => Tools::getValue('text', null),
            ));
        }

        if (Tools::getIsset('success'))
            $this->context->smarty->assign('confirmation', true);

        $this->setTemplate('list.tpl');
    }

    /**
     * Validate and save new review
     *
     * @return array
     */
    public function postProcess()
    {
        if (Tools::getIsset('submitNewReview')) {
            $review_add_success = false;

            $customer_name = Tools::getValue('customer_name', null);
            $email = Tools::getValue('email', null);
            $grade = Tools::getValue('grade', null);
            $text = Tools::getValue('text', null);

            if (!$customer_name)
                $this->errors[] = Tools::displayError('Error in customer name.');

            if (!$email || !Validate::isEmail($email))
                $this->errors[] = Tools::displayError('Error in email field.');

            if (!$grade)
                $this->errors[] = Tools::displayError('Please provide a grade.');

            if (!$text)
                $this->errors[] = Tools::displayError('Error in review text field.');

            if (!$this->errors) {
                $review = new ShopReview();
                $review->customer_name = $customer_name;
                $review->email = $email;
                $review->grade = $grade;
                $review->text = $text;

                $review_add_success = $review->add();

                if (!$review_add_success) {
                    $this->errors[] = Tools::displayError('An error occurred while saving review.');
                } else {
                    Tools::redirect('index.php?controller='.$this->page_name.'&success');
                }
            } // if (!this->errors)
        } // if (Tools::getIsset('submitNewReview'))
    }
}
