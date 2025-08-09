<?php

use Symfony\Component\Yaml\Yaml;

// Common utility helpers
class Util
{
  private static $config = null;

  public static function config() : array
  {
    if( self::$config === null )
    {
      $path = 'config.yml';
      if( ! file_exists($path) )
      {
        self::$config = [ 'dataFileName' => '-this' ];
      }
      else
      {
        self::$config = Yaml::parse(file_get_contents($path));
      }
    }
    return self::$config;
  }

  public static function dataFileName() : string
  {
    $cfg = self::config();
    return isset($cfg['dataFileName']) ? (string)$cfg['dataFileName'] : '-this';
  }

  public static function now() : string
  {
    return date('Y-m-d H:i:s');
  }

  public static function nowCompact() : string
  {
    return date('ymdHis');
  }

  public static function generateId( string $name ) : string
  {
    $words = preg_split('/\s+/', trim($name));
    $parts = [];
    foreach( $words as $w )
    {
      if( $w === '' ) continue;
      $w = preg_replace('/[^A-Za-z0-9]/', '', $w);
      if( $w === '' ) continue;
      $parts[] = strtoupper(substr($w, 0, 1)) . strtolower(substr($w, 1));
    }
    $title = implode('', $parts);
    if( $title === '' ) $title = 'Item';
    return "$title-Default-" . self::nowCompact();
  }

  public static function ensureDir( string $dir ) : void
  {
    if( $dir !== '' && ! is_dir($dir) )
    {
      mkdir($dir, 0777, true);
    }
  }

  public static function joinPath( string ...$segments ) : string
  {
    $out = [];
    foreach( $segments as $s )
    {
      if( $s === '' ) continue;
      $out[] = trim($s, "/\\");
    }
    $p = implode(DIRECTORY_SEPARATOR, $out);
    if( substr($segments[0] ?? '', 0, 1) === DIRECTORY_SEPARATOR )
    {
      return DIRECTORY_SEPARATOR . $p;
    }
    return $p;
  }

  public static function sanitizeName( string $name ) : string
  {
    $s = trim($name);
    $s = preg_replace('/[\\r\\n]+/', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    $s = preg_replace('/[\\\\\/:*?\"<>|]/', '-', $s);
    $s = trim($s, ' .-');
    if( $s === '' ) $s = 'item-' . self::nowCompact();
    return $s;
  }

  public static function readFrontMatter( string $filePath ) : array
  {
    $content = file_exists($filePath) ? file_get_contents($filePath) : '';
    $fm = [];
    $body = '';
    if( preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $m) )
    {
      $fm = Yaml::parse($m[1]) ?? [];
      $body = $m[2];
    }
    elseif( preg_match('/^---\s*\n(.*?)\n---\s*$/s', $content, $m) )
    {
      $fm = Yaml::parse($m[1]) ?? [];
      $body = '';
    }
    else
    {
      // No front matter, treat all as body
      $fm = [];
      $body = $content;
    }
    return [ $fm, $body ];
  }

  public static function writeFrontMatter( string $filePath, array $fm, string $body ) : void
  {
    $yaml = Yaml::dump($fm, 2, 2);
    $content = "---\n$yaml---\n" . $body;
    file_put_contents($filePath, $content);
  }

  public static function isMarkdown( string $file ) : bool
  {
    return (bool)preg_match('/\\.(md|markdown)$/i', $file);
  }

  public static function isText( string $file ) : bool
  {
    return (bool)preg_match('/\\.(md|markdown|txt|yml|yaml|json|csv|log)$/i', $file);
  }

  public static function fileSizeNice( int $bytes ) : string
  {
    $units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
    $i = 0;
    $size = $bytes;
    while( $size >= 1024 && $i < count($units) - 1 )
    {
      $size /= 1024; $i++;
    }
    return round($size, 1) . ' ' . $units[$i];
  }

  public static function readJson( string $path, array $fallback = [] ) : array
  {
    if( ! file_exists($path) ) return $fallback;
    $s = file_get_contents($path);
    $d = json_decode($s, true);
    if( ! is_array($d) ) return $fallback;
    return $d;
  }

  public static function writeJson( string $path, array $data ) : void
  {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
  }

  public static function ensureDataRoot() : string
  {
    $root = 'data';
    self::ensureDir($root);
    return $root;
  }

  public static function relPathWithin( string $base, string $target ) : string
  {
    // Normalize to forward slashes
    $base = rtrim(str_replace('\\\\', '/', $base), '/');
    $target = str_replace('\\\\', '/', $target);
    if( strpos($target, $base . '/') === 0 )
    {
      return substr($target, strlen($base));
    }
    return $target;
  }
}
