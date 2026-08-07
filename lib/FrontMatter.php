<?php

namespace Sys;

use Symfony\Component\Yaml\Yaml;

/**
 * Splits and joins markdown files with yml front matter.
 *
 * A file without front matter is not an object, so parse() reports it
 * with data === null instead of inventing an empty header.
 */
class FrontMatter
{
  /**
   * @return array{data: ?array, body: string}
   */
  public static function parse( string $raw ) : array
  {
    $text = str_replace("\r\n", "\n", $raw);

    if( strpos($text, "---\n") !== 0 )
      return ['data' => null, 'body' => $text];

    $end = strpos($text, "\n---", 3);

    if( $end === false )
      return ['data' => null, 'body' => $text];

    $head = substr($text, 4, $end - 3);
    $body = substr($text, $end + 4);

    try {

      $data = Yaml::parse($head);
    }
    catch( \Exception $e ) {
      return ['data' => null, 'body' => $text];
    }

    if( ! is_array($data))
      return ['data' => null, 'body' => $text];

    // Trimmed both ends so a read/write round trip does not grow the file
    return ['data' => $data, 'body' => trim($body, "\n")];
  }

  public static function build( array $data, string $body ) : string
  {
    $head = Yaml::dump($data, 4, 2);
    $body = rtrim(str_replace("\r\n", "\n", $body), "\n");

    return "---\n{$head}---\n\n{$body}\n";
  }
}
