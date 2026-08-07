<?php

/**
 * Person card: role and mail address are what one looks for in a list.
 */
class PersonListRenderer extends \Sys\ListRenderer
{
  protected function meta() : string
  {
    $obj   = $this->obj->display();
    $parts = [];

    if( $obj->field('role', '') !== '' )
      $parts[] = $this->esc($obj->field('role'));

    if( $obj->field('email', '') !== '' )
      $parts[] = '<span class="mono">' . $this->esc($obj->field('email')) . '</span>';

    return implode(' <span class="sep">·</span> ', $parts);
  }
}
