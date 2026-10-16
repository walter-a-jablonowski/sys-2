<?php

namespace Sys;

use Symfony\Component\Yaml\Yaml;

/**
 * Settings of the user working with the app (data/users/USER/settings.yml),
 * written back on every change.
 */
class UserSettings
{
  private string $file;
  private array $data;

  public function __construct( string $file )
  {
    $this->file = $file;
    $this->data = Config::read($file);
  }

  public function get( string $key, $default = null )
  {
    return $this->data[$key] ?? $default;
  }

  public function set( string $key, $value ) : void
  {
    $this->data[$key] = $value;

    if( ! is_dir(dirname($this->file)))
      mkdir(dirname($this->file), 0777, true);

    file_put_contents($this->file, Yaml::dump($this->data, 4, 2));
  }
}
