<?php

use Symfony\Component\Yaml\Yaml;

// -------------------------------------------------
// Config
// -------------------------------------------------

function app_config() : array
{
  static $cfg = null;
  if( $cfg === null ) {
    $cfg = [ 'dataFileName' => '-this' ];
    if( file_exists(CONFIG_FILE) ) {
      $yaml = Yaml::parseFile(CONFIG_FILE);
      $cfg   = array_merge($cfg, $yaml);
    }
  }
  return $cfg;
}

// -------------------------------------------------
// Markdown front matter helpers
// -------------------------------------------------

function parse_front_matter( string $raw ) : array
{
  if( preg_match('/^---\s*(.*?)\s*---/s', $raw, $m) ) {
    $yaml = Yaml::parse(trim($m[1]));
    $body = trim(substr($raw, strlen($m[0])));
    return [$yaml, $body];
  }
  return [[], $raw];
}

function build_front_matter( array $yaml, string $body ) : string
{
  $front = Yaml::dump($yaml, 2, 2);
  return "---\n$front---\n$body";
}

// -------------------------------------------------
// ID generation from name
// -------------------------------------------------

function generate_id( string $name ) : string
{
  $words = preg_split('/\s+/', trim($name));
  $abbr  = '';
  foreach( $words as $w ) {
    $abbr .= strtoupper(substr($w,0,1));
  }
  return preg_replace('/[^A-Z0-9]/', '', $abbr) . '-Default-' . date('ymdHis');
}

// -------------------------------------------------
// Type discovery
// -------------------------------------------------

// -------------------------------------------------
// Type definitions loader
// -------------------------------------------------

function list_types() : array
{
  $types = [];
  $global = Yaml::parseFile(TYPES_DIR . '/def.yml');
  foreach( glob(TYPES_DIR . '/*', GLOB_ONLYDIR) as $dir ) {
    $id = basename($dir);
    $defFile = "$dir/def.yml";
    if( file_exists($defFile) ) {
      $types[$id] = Yaml::parseFile($defFile);
      $types[$id]['_dir'] = $dir;
    }
  }
  return [$global, $types];
}
