<?php

use Cabbie\Sms;

class Route
{

    public function all($page)
    {
        $page = trim($page, '/');

        if (empty($page)) {
            $this->index();
            return;
        }

        if (!method_exists($this, $page)) {
            $this->notFound();
            return;
        }

        $this->$page();
    }

    public function notFound()
    {
        // 404 error here
    }

    public function index()
    {
        include_once 'views/index.html';
    }

    public function privacy()
    {
        include_once 'views/privacy.html';
    }

    public function receiver()
    {
        //
        $sms = new Sms;
        $sms->processIncoming($_POST);
    }
}
