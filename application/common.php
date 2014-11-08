<?php

function base_path() {
  static $base_path;

  if (!isset($base_path)) {
    $file_path = $_SERVER['SCRIPT_NAME'];
    $document_root = realpath($_SERVER['DOCUMENT_ROOT']);

    $base_path = str_replace($document_root, '', $file_path);
    $base_path = explode(DIRECTORY_SEPARATOR, $base_path);
    array_pop($base_path);
    $base_path = implode(DIRECTORY_SEPARATOR, $base_path);
  }

  return $base_path;
}

function base_url() {
  static $base_url;
  
  if (!isset($base_url)) {
    $base_url = protocol() . '://' . $_SERVER['HTTP_HOST'];
  }
  
  return $base_url;
}

function is_secure() {
  return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' || $_SERVER['SERVER_PORT'] == 443)
}

function protocol() {
  return (is_secure()) ? 'https' : 'http';
}