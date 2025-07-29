<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

class Config
{
  private static $config = null;

  public static function get( $key = null )
  {
    if( self::$config === null )
    {
      try {
        self::$config = Yaml::parseFile('config.yml');
      }
      catch( Exception $e ) {
        throw new Exception("Failed to load config.yml: " . $e->getMessage());
      }
    }

    if( $key === null )
      return self::$config;

    return self::$config[$key] ?? null;
  }
}
