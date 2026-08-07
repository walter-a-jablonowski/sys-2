<?php

namespace Sys;

use Symfony\Component\Yaml\Yaml;

/**
 * App configuration (config.yml) and database configuration (<db>/.sys/config.yml).
 */
class Config
{
  // Database defaults, overridden by the database's own .sys/config.yml
  private const DB_DEFAULTS = [
    'thisFile'    => '-this',
    'linkMarker'  => ' (lnk)',
    'defaultType' => 'Info',
    'pageSize'    => 50,
    'sort'        => ['by' => 'created', 'dir' => 'desc']
  ];

  public array $app;
  public array $db;

  public function __construct( string $appFile, string $dbConfigFile )
  {
    $this->app = self::read($appFile);
    $this->db  = array_replace(self::DB_DEFAULTS, self::read($dbConfigFile));
  }

  public function app( string $key, $default = null )
  {
    return $this->app[$key] ?? $default;
  }

  public function db( string $key, $default = null )
  {
    return $this->db[$key] ?? $default;
  }

  /**
   * Reads a yml file, missing or empty files give an empty array.
   */
  public static function read( string $file ) : array
  {
    if( ! is_file($file))
      return [];

    $data = Yaml::parseFile($file);
    return is_array($data) ? $data : [];
  }
}
