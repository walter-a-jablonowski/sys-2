<?php

namespace Sys;

/**
 * Application error carrying the http status the ajax layer answers with.
 */
class Failure extends \RuntimeException
{
  public int $status;
  public array $info;

  public function __construct( string $message, int $status = 400, array $info = [] )
  {
    parent::__construct($message);
    $this->status = $status;
    $this->info   = $info;
  }

  public static function notFound( string $what ) : self
  {
    return new self("Not found: $what", 404);
  }

  public static function conflict( string $message, array $info = [] ) : self
  {
    return new self($message, 409, $info);
  }

  public static function invalid( string $message, array $info = [] ) : self
  {
    return new self($message, 422, $info);
  }

  public static function collision( string $message ) : self
  {
    return new self($message, 423);
  }
}
