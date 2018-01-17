<?php

class simple_faqListModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();

        $this->context = Context::getContext();

        include_once $this->module->getLocalPath().'models/Question.php';
    }

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $questions = Question::getQuestions();

        $this->context->smarty->assign(array(
            'questions' => $questions,
            'email' => $this->context->cookie->email ? $this->context->cookie->email : '',
            'errors' => $this->errors,
        ));

        if ($this->errors) {
            $this->context->smarty->assign(array(
                'post_name' => Tools::getValue('customer_name', null),
                'post_email' => Tools::getValue('email', null),
                'post_question' => Tools::getValue('question', null),
            ));
        }

        if (Tools::getIsset('success'))
            $this->context->smarty->assign('confirmation', true);

        $this->setTemplate('list.tpl');
    }

    /**
     * Validate and save new question
     *
     * @return array
     */
    public function postProcess()
    {
        if (Tools::getIsset('submitNewQuestion')) {
            $question_add_success = false;

            $customer_name = Tools::getValue('customer_name', null);
            $email = Tools::getValue('email', null);
            $question_text = Tools::getValue('question', null);

            if (!$customer_name)
                $this->errors[] = Tools::displayError('Error in customer name.');

            if (!$email || !Validate::isEmail($email))
                $this->errors[] = Tools::displayError('Error in email field.');

            if (!$question_text)
                $this->errors[] = Tools::displayError('Error in question question field.');

            if (!$this->errors) {
                $question = new Question();
                $question->customer_name = $customer_name;
                $question->email = $email;
                $question->question = $question_text;

                $question_add_success = $question->add();

                if (!$question_add_success) {
                    $this->errors[] = Tools::displayError('An error occurred while saving question.');
                } else {
                    Tools::redirect('index.php?controller='.$this->page_name.'&success');
                }
            } // if (!this->errors)
        } // if (Tools::getIsset('submitNewQuestion'))
    }
}
