<?php

namespace Sys;

/**
 * Human readable file sizes, used by the File renderers.
 */
class FileSize
{
  public static function human( string $abs ) : string
  {
    if( ! is_file($abs))
      return '';

    $bytes = (int) filesize($abs);
    $units = ['B', 'kB', 'MB', 'GB'];
    $i     = 0;

    while( $bytes >= 1024 && $i < count($units) - 1 )
    {
      $bytes /= 1024;
      $i++;
    }

    return ($i === 0 ? $bytes : round($bytes, $bytes < 10 ? 1 : 0)) . ' ' . $units[$i];
  }
}
