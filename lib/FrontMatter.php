<?php

namespace Sys;

use Symfony\Component\Yaml\Yaml;

/**
 * Splits and joins markdown files with yml front matter.
 *
 * A file without front matter is reported with data === null instead of an
 * invented empty header, "broken" tells a head that does not parse apart
 * from none at all.
 */
class FrontMatter
{
  /**
   * @return array{data: ?array, body: string, broken: bool}
   */
  public static function parse( string $raw ) : array
  {
    $text = str_replace("\r\n", "\n", $raw);
    $none = ['data' => null, 'body' => $text, 'broken' => false];

    if( strpos($text, "---\n") !== 0 )
      return $none;

    $broken = ['broken' => true] + $none;
    $end    = strpos($text, "\n---", 3);

    if( $end === false )
      return $broken;

    $head = substr($text, 4, $end - 3);
    $body = substr($text, $end + 4);

    try {

      $data = Yaml::parse($head) ?? [];    // an empty head is no header yet, not a broken one
    }
    catch( \Exception $e ) {
      return $broken;
    }

    if( ! is_array($data))
      return $broken;

    // Trimmed both ends so a read/write round trip does not grow the file
    return ['data' => $data, 'body' => trim($body, "\n"), 'broken' => false];
  }

  public static function build( array $data, string $body ) : string
  {
    $head = Yaml::dump($data, 4, 2);
    $body = rtrim(str_replace("\r\n", "\n", $body), "\n");

    return "---\n{$head}---\n\n{$body}\n";
  }
}
