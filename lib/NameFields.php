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
 *
 * A field may also lead the name, decide the type of what carries it, order
 * the lists and select entries, e.g. "2 - My task":
 *
 *   - field: 'prio'
 *     values: ['1', '2', '3', '4', '5']   # in the order lists are sorted by
 *     labels: { '1': 'Selected', '3': 'High' }
 *     match: '{value}\s*-\s*'
 *     format: '{value} - '
 *     at: 'start'                         # leads the name, nothing needs to follow it but the title
 *     type: 'Activity'                    # unless the front matter names one
 *     sort: true                          # lists are ordered by it, entries without come last
 *     selected: { '1': 'global', '2': 'local' }   # global: listed under "Selected", wherever it lives
 */
class NameFields
{
  private const ROLES    = ['', 'id', 'link'];
  private const SCOPES   = ['global', 'local'];
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
      $start  = ($rule['at'] ?? '') === 'start';
      $chosen = array_map('strval', (array) ($rule['selected'] ?? []));

      // Fixed values are found as written by format, unless match places them itself. None at all: nothing matches
      if( $values !== null && ($match !== '' || $format !== ''))
      {
        $any   = $values ? '(' . implode('|', array_map(fn( $one ) => preg_quote($one, '~'), $values)) . ')' : '(?!)';
        $match = $match !== '' ? str_replace('{value}', $any, $match) : str_replace(preg_quote('{value}', '~'), $any, preg_quote($format, '~'));
      }

      // A leading field only has to start the name, any other stands on its own
      $regex = $start ? "~^\s*(?:{$match})~u" : "~(?<=^|\s)(?:{$match})(?=\s|$)~u";

      if( $field === '' || in_array($field, self::RESERVED, true) || isset($this->rules[$field]))
        throw new Failure("nameFields: field \"$field\" is missing, reserved or defined twice", 500);

      if( $match === '' || @preg_match($regex, '') === false )
        throw new Failure("nameFields: \"$field\" needs a valid match, or values and a format", 500);

      if( ! in_array($role, self::ROLES, true) || ($role !== '' && ($list || $this->fieldOf($role) !== null)))
        throw new Failure("nameFields: \"$field\" has an unknown role, a role on a list or a role taken twice", 500);

      if( ! in_array($rule['at'] ?? '', ['', 'start'], true) || ($start && $list))
        throw new Failure("nameFields: \"$field\" has an unknown \"at\" or leads the name as a list", 500);

      if(( ! empty($rule['sort']) || $chosen ) && ($values === null || array_diff(array_keys($chosen), $values) || array_diff($chosen, self::SCOPES)))
        throw new Failure("nameFields: \"$field\" sorts or selects without values, or selects an unknown value or scope", 500);

      $this->rules[$field] = [
        'regex'  => $regex,
        'format' => $format,
        'values'   => $values,     // null: any value the match takes
        'labels'   => array_map('strval', (array) ($rule['labels'] ?? [])),
        'role'     => $role,
        'list'     => $list,
        'start'    => $start,
        'type'     => (string) ($rule['type'] ?? ''),
        'sort'     => ! empty($rule['sort']),
        'selected' => $chosen,     // value -> 'global' | 'local'
        'label'    => (string) ($rule['label'] ?? ucfirst($field))
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
   * The fixed values a name carries, e.g. its tags or its prio: what cards
   * show and the list filter matches.
   *
   * @return array[]  each with field, value, label, selected ('', 'global', 'local'), sort,
   *                   start (leads the name, so it is shown before the title)
   */
  public function fixedValues( array $data ) : array
  {
    $out = [];

    foreach( $this->rules as $field => $rule )
    {
      if( $rule['values'] === null )
        continue;

      foreach( (array) ($data[$field] ?? []) as $value )
      {
        $out[] = [
          'field'    => $field,
          'value'    => $value,
          'label'    => $rule['labels'][$value] ?? $value,
          'selected' => $rule['selected'][$value] ?? '',
          'sort'     => $rule['sort'],
          'start'    => $rule['start']
        ];
      }
    }

    return $out;
  }

  /**
   * The type a name gives what carries it, '' when it gives none.
   */
  public function typeOf( array $data ) : string
  {
    foreach( $this->rules as $field => $rule )
    {
      if( $rule['type'] !== '' && isset($data[$field]))
        return $rule['type'];
    }

    return '';
  }

  /**
   * Place in a sorted list: the position of the value in the values of the
   * sorting field, entries without one after all others.
   */
  public function rank( array $data ) : int
  {
    foreach( $this->rules as $field => $rule )
    {
      if( $rule['sort'] )
      {
        $at = array_search((string) ($data[$field] ?? ''), $rule['values'], true);
        return $at === false ? count($rule['values']) : $at;
      }
    }

    return 0;
  }

  /**
   * Whether a name can select an entry for the whole database, which is
   * what brings the "Selected" list along.
   */
  public function selectsGlobally() : bool
  {
    foreach( $this->rules as $rule )
    {
      if( in_array('global', $rule['selected'], true))
        return true;
    }

    return false;
  }

  public function isSelectedGlobally( array $data ) : bool
  {
    foreach( $this->fixedValues($data) as $entry )
    {
      if( $entry['selected'] === 'global' )
        return true;
    }

    return false;
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
   * Tokens in the order of the rules: those leading the name, or those
   * behind the title.
   */
  public function flatten( array $tokens, bool $start ) : array
  {
    $out = [];

    foreach( $this->rules as $field => $rule )
    {
      if( $rule['start'] === $start )
        $out = array_merge($out, $tokens[$field] ?? []);
    }

    return $out;
  }
}
