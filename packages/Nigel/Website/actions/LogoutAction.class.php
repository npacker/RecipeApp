<?php

use nFramework\Action;
use nFramework\Context;
use nFramework\View\Template;
use nFramework\Response;

class LogoutAction extends Action {

  public function execute(Context $context) {
    $session = new Session();
    $session->destroy();
    $response = new Response();

    return $response->redirect('http://' . base_url() . base_path() . '/');
  }

}