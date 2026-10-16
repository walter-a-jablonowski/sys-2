<?php

namespace Sys;

/**
 * One entry type: what its objects are made of, read from types/MY_TYPE.yml.
 * How they are shown is up to the viewers in type_viewer/MY_TYPE.
 */
class Type
{
  public string $name;
  public array $config;

  public function __construct( string $name, array $config )
  {
    $this->name   = $name;
    $this->config = $config;
  }

  public function label() : string
  {
    return $this->config['label'] ?? $this->name;
  }

  public function icon() : string
  {
    return $this->config['icon'] ?? 'entry';
  }

  public function get( string $key, $default = null )
  {
    return $this->config[$key] ?? $default;
  }

  public function fields() : array
  {
    return $this->config['fields'] ?? [];
  }

  public function tabs() : array
  {
    return $this->config['tabs'] ?? [];
  }

  /**
   * Types that can be created in a tab, plus the special "__link:Type" entry.
   */
  public function createOptions( string $tab ) : array
  {
    return $this->config['create'][$tab] ?? [];
  }

  /**
   * Name of the viewer used unless the user picked another one.
   */
  public function viewer() : string
  {
    return $this->config['viewer'] ?? 'default';
  }

  /**
   * Generates an id from the type's pattern. Ids are free strings, this is
   * only the generator for new objects (plan §2.3).
   */
  public function newId( string $title ) : string
  {
    $pattern = $this->config['idPattern'] ?? '{slug}-{YYYY}-{MM}-{DD}-{HHmm}';

    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug);
    $slug = trim((string) $slug, '-');

    if( $slug === '' )
      $slug = strtolower($this->name);

    return str_replace(
      ['{slug}', '{type}', '{YYYY}', '{MM}', '{DD}', '{HHmm}'],
      [$slug, strtolower($this->name), date('Y'), date('m'), date('d'), date('Hi')],
      $pattern
    );
  }

  public function newName() : string
  {
    return 'New ' . $this->label();
  }
}
