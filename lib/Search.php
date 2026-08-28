<?php

namespace Sys;

/**
 * Finds an object anywhere in the hierarchy by typing part of its path.
 *
 * The index already holds the path of every object and a title is nothing
 * but the last segment of one (see Obj), so searching needs no store of its
 * own: the whole hierarchy is one list of paths to match against.
 */
class Search
{
  // A hit right after one of these starts a word
  private const BREAKS = ['/', ' ', '-', '_', '.'];

  private App $app;

  public function __construct( App $app )
  {
    $this->app = $app;
  }

  /**
   * Best matches first.
   *
   * @return Obj[]
   */
  public function run( string $query, int $limit = 20 ) : array
  {
    $needle = mb_strtolower(trim($query));

    if( $needle === '' )
      return [];

    $hits = [];

    foreach( $this->app->index->all() as $id => $entry )
    {
      $score = $this->score((string) $entry['path'], $needle);

      if( $score >= 0 )
        $hits[] = ['id' => $id, 'score' => $score, 'mtime' => (int) $entry['mtime'], 'path' => (string) $entry['path']];
    }

    usort($hits, function( array $a, array $b ) {

      return [$b['score'], $b['mtime'], $a['path']] <=> [$a['score'], $a['mtime'], $b['path']];
    });

    $out = [];

    foreach( array_slice($hits, 0, $limit) as $hit )
    {
      $obj = $this->app->index->obj($hit['id']);

      if( $obj !== null )
        $out[] = $obj;
    }

    return $out;
  }

  // Internals

  /**
   * Every character of the needle somewhere in the path, in order.
   *
   * What separates a good hit from a poor one is where the characters land,
   * not that they land at all: neighbours score more than scattered hits,
   * word starts more than the middle of a word, and the title more than an
   * ancestor the user only typed to get past.
   *
   * @return int -1 when the path does not match at all
   */
  private function score( string $path, string $needle ) : int
  {
    $hay   = mb_strtolower($path);
    $leaf  = ($cut = mb_strrpos($hay, '/')) === false ? 0 : $cut + 1;
    $score = 0;
    $at    = 0;
    $run   = 0;
    $first = -1;

    foreach( preg_split('//u', $needle, -1, PREG_SPLIT_NO_EMPTY) as $char )
    {
      $found = mb_strpos($hay, $char, $at);

      if( $found === false )
        return -1;

      if( $first < 0 )
        $first = $found;

      $run    = $found === $at && $at > 0 ? $run + 1 : 0;
      $score += 1 + $run * 4;

      if( $found === 0 || in_array(mb_substr($hay, $found - 1, 1), self::BREAKS, true))
        $score += 6;

      $at = $found + 1;
    }

    // Typing the title itself is the common case and deserves to win
    if( $first >= $leaf )
      $score += 30;

    // Of two equally good hits the shallower one is the more likely target
    return max(0, $score - substr_count($path, '/') * 2);
  }
}
