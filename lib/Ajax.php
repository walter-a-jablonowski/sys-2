<?php

namespace Sys;

/**
 * Routes an ajax call to a handler in /ajax or to a viewer's own /ajax folder,
 * and turns whatever it returns into a json response.
 */
class Ajax
{
  private App $app;
  private array $params;

  public function __construct( App $app )
  {
    $this->app   = $app;
    $this->params = $_GET + $_POST;

    // Saves send json, reads send plain query parameters
    if( strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false )
    {
      $body = json_decode((string) file_get_contents('php://input'), true);

      if( is_array($body))
        $this->params = $body + $this->params;
    }
  }

  public function run() : void
  {
    header('Content-Type: application/json; charset=utf-8');

    try {

      echo json_encode($this->dispatch(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    catch( Failure $e ) {
      http_response_code($e->status);
      echo json_encode(['error' => $e->getMessage()] + $e->info);
    }
    catch( \Throwable $e ) {
      http_response_code(500);
      echo json_encode(['error' => $e->getMessage(), 'where' => basename($e->getFile()) . ':' . $e->getLine()]);
    }
  }

  // Parameters

  public function param( string $key, $default = '' )
  {
    $value = $this->params[$key] ?? $default;

    return is_string($value) ? trim($value) : $value;
  }

  public function intParam( string $key, int $default = 0 ) : int
  {
    return (int) ($this->params[$key] ?? $default);
  }

  public function arrayParam( string $key ) : array
  {
    $value = $this->params[$key] ?? [];

    return is_array($value) ? $value : [];
  }

  /**
   * Loads the object a call is about, by path or by id.
   */
  public function obj( string $key = 'path' ) : Obj
  {
    $id = (string) $this->param('id', '');

    if( $key === 'path' && $id !== '' )
    {
      $obj = $this->app->index->obj($id);

      if( $obj !== null )
        return $obj;
    }

    $path = (string) $this->param($key, '');
    $obj  = $this->app->store->get($path);

    if( $obj === null )
      throw Failure::notFound($path === '' ? '(root)' : $path);

    return $obj;
  }

  // Internals

  private function dispatch() : array
  {
    $action = (string) $this->param('a', '');

    if( ! preg_match('/^[a-z][a-z_]*$/', $action))
      throw Failure::invalid('Unknown action');

    $file = $action === 'viewer'
      ? $this->viewerHandler()
      : "{$this->app->root}/ajax/{$action}.php";

    if( ! is_file($file))
      throw Failure::notFound("action: $action");

    $app  = $this->app;
    $ajax = $this;

    $result = require $file;

    return is_array($result) ? $result : [];
  }

  /**
   * Viewer specific endpoint: type_viewer/MY_TYPE/MY_VIEWER/ajax/MY_ACTION.php
   */
  private function viewerHandler() : string
  {
    $type   = (string) $this->param('type', '');
    $viewer = (string) $this->param('viewer', '');
    $fn     = (string) $this->param('fn', '');

    $name = '/^[A-Za-z][A-Za-z0-9]*$/';

    if( ! preg_match($name, $type) || ! preg_match($name, $viewer) || ! preg_match('/^[a-z][a-z_]*$/', $fn))
      throw Failure::invalid('Unknown viewer action');

    return "{$this->app->root}/type_viewer/{$type}/{$viewer}/ajax/{$fn}.php";
  }
}
