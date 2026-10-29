<?php

namespace Sys;

/**
 * Fields carried in a file or folder name, defined per database under
 * "nameFields" in <db>/.sys/config.yml.
 *
 * The rules run in their order and each one takes its tokens out of the name
 * before the next one looks, what is left is the title. A token stands on its
 * own: it starts the name or follows a blank, and ends it or is followed by one.
 *
 *   - field: 'id2'
 *     match: '\(?(?:id-|#)([^\s()]+)\)?'   # group 1 is the value, else the whole token
 *     format: '(id-{value})'              # how an edited value is written, none: read-only
 *     role: 'id'                          # 'id': alternative id, 'link': links to that id
 *     list: true                          # every token counts, not only the first
 *     label: 'Id 2'
 *
 * A field may take fixed values only, e.g. tags. Names put no special mark
 * on such a field, so its strings have to be named: "(best)" is a tag only
 * because "best" is listed, "(some text)" stays in the title. The tokens are
 * written as "format" says, a "match" may place them with {value} instead:
 *
 *   - field: 'tags'
 *     values: ['best', 'draft']           # a type may add its own (ObjPath::fields)
 *     format: '({value})'                 # also how they are found
 *     list: true
 */
class NameFields
{
  private const ROLES    = ['', 'id', 'link'];
  private const RESERVED = ['title', 'body', 'id', 'type', 'created', 'modified', 'target'];

  private array $rules = [];     // field -> rule, in the order they run

  public function __construct( array $rules )
  {
    foreach( $rules as $rule )
    {
      $field  = (string) ($rule['field'] ?? '');
      $match  = (string) ($rule['match'] ?? '');
      $format = (string) ($rule['format'] ?? '');
      $values = isset($rule['values']) ? array_values(array_filter(array_map('strval', (array) $rule['values']), 'strlen')) : null;
      $role   = (string) ($rule['role'] ?? '');
      $list   = (bool) ($rule['list'] ?? false);

      // Fixed values are found as written by format, unless match places them itself. None at all: nothing matches
      if( $values !== null && ($match !== '' || $format !== ''))
      {
        $any   = $values ? '(' . implode('|', array_map(fn( $one ) => preg_quote($one, '~'), $values)) . ')' : '(?!)';
        $match = $match !== '' ? str_replace('{value}', $any, $match) : str_replace(preg_quote('{value}', '~'), $any, preg_quote($format, '~'));
      }

      $regex = "~(?<=^|\s)(?:{$match})(?=\s|$)~u";

      if( $field === '' || in_array($field, self::RESERVED, true) || isset($this->rules[$field]))
        throw new Failure("nameFields: field \"$field\" is missing, reserved or defined twice", 500);

      if( $match === '' || @preg_match($regex, '') === false )
        throw new Failure("nameFields: \"$field\" needs a valid match, or values and a format", 500);

      if( ! in_array($role, self::ROLES, true) || ($role !== '' && ($list || $this->fieldOf($role) !== null)))
        throw new Failure("nameFields: \"$field\" has an unknown role, a role on a list or a role taken twice", 500);

      $this->rules[$field] = [
        'regex'  => $regex,
        'format' => $format,
        'values' => $values,     // null: any value the match takes
        'role'   => $role,
        'list'   => $list,
        'label'  => (string) ($rule['label'] ?? ucfirst($field))
      ];
    }
  }

  public function rules() : array
  {
    return $this->rules;
  }

  public function has( string $field ) : bool
  {
    return isset($this->rules[$field]);
  }

  /**
   * The field playing a role, null when the database defines none.
   */
  public function fieldOf( string $role ) : ?string
  {
    foreach( $this->rules as $field => $rule )
    {
      if( $rule['role'] === $role )
        return $field;
    }

    return null;
  }

  public function roleValue( array $data, string $role ) : string
  {
    $field = $this->fieldOf($role);

    return $field === null ? '' : (string) ($data[$field] ?? '');
  }

  /**
   * The fixed values a name carries, e.g. its tags: what cards show and the
   * list filter matches.
   *
   * @return string[]
   */
  public function fixedValues( array $data ) : array
  {
    $out = [];

    foreach( $this->rules as $field => $rule )
    {
      if( $rule['values'] !== null )
        $out = array_merge($out, (array) ($data[$field] ?? []));
    }

    return $out;
  }

  /**
   * @return array{title: string, data: array, tokens: array}  tokens: field -> tokens as written
   */
  public function parse( string $name ) : array
  {
    $data   = [];
    $tokens = [];

    foreach( $this->rules as $field => $rule )
    {
      $name = preg_replace_callback($rule['regex'], function( array $m ) use ( $field, $rule, &$data, &$tokens ) {

        $value = $m[1] ?? $m[0];

        $tokens[$field][] = $m[0];

        if( $rule['list'] )
          $data[$field][] = $value;
        else
          $data[$field] = $value;

        return ' ';
      }, $name, $rule['list'] ? -1 : 1);
    }

    return ['title' => trim(preg_replace('/\s+/u', ' ', $name)), 'data' => $data, 'tokens' => $tokens];
  }

  /**
   * An editor value as the field holds it: a list field takes "a, b".
   */
  public function normalize( string $field, $value )
  {
    if( ! $this->rules[$field]['list'] )
      return trim((string) $value);

    $parts = is_array($value) ? $value : explode(',', (string) $value);

    return array_values(array_filter(array_map('trim', $parts), 'strlen'));
  }

  /**
   * Tokens writing a value into the name, one per list entry, none when empty.
   * A field of fixed values takes no other: written anyway, it would only
   * be read back as part of the title.
   */
  public function format( string $field, $value ) : array
  {
    $rule = $this->rules[$field];

    if( $rule['format'] === '' )
      throw Failure::invalid("{$rule['label']} is read from the name and cannot be edited", ['field' => $field]);

    $values  = array_filter((array) $value, 'strlen');
    $unknown = $rule['values'] === null ? [] : array_diff($values, $rule['values']);

    if( $unknown )
      throw Failure::invalid('"' . reset($unknown) . "\" is not one of the {$rule['label']}: " . implode(', ', $rule['values']), ['field' => $field]);

    return array_map(fn( $one ) => str_replace('{value}', $one, $rule['format']), array_values($values));
  }

  /**
   * Tokens of all fields in the order of the rules.
   */
  public function flatten( array $tokens ) : array
  {
    $out = [];

    foreach( array_keys($this->rules) as $field )
      $out = array_merge($out, $tokens[$field] ?? []);

    return $out;
  }
}
