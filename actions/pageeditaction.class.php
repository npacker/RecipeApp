<?php

class PageEditAction extends Action {

  public function execute(ActionContext $context) {
    $model = new PageMapper();
    $page = new Page();
    $id = $context->get('path_argument');
    $title = $context->get('title');
    $content = $context->get('content');
    $page->setId($id);

    if (isset($title) && isset($content)) {
      $page->setTitle($title);
      $page->setContent($content);
      $model->update($page);
    } else {
      $model->find($page);
    }

    if (!$page->getTitle()) {
      throw new Exception('The page could not be found.');
    }

    $variables = (array) $page;
    $variables['action'] = 'http://' . base_url() . base_path() . '/page/edit/' . $id;

    $template = new Template('page/edit', $variables);
    $template->addScript(array('jquery','ckeditor/ckeditor','editor'));

    $data['title'] = "Editing page <em>{$page->getTitle()}</em>";
    $data['content'] = $template;
    $data['template'] = 'html';

    return $data;
  }

}