<?php

/**
 * Activity card: status first, then how much is inside and when it happens.
 */
class ActivityListRenderer extends \Sys\ListRenderer
{
  protected function badges() : string
  {
    $status = (string) $this->obj->display()->field('status', '');

    if( $status === '' )
      return '';

    return '<span class="status status-' . $this->esc($status) . '">' . $this->esc($status) . '</span>';
  }

  protected function meta() : string
  {
    $obj   = $this->obj->display();
    $parts = [];

    if( $obj->isFolder )
    {
      $n = $this->childCount();
      $parts[] = $n === 1 ? '1 entry' : "$n entries";
    }

    $schedule = $obj->field('schedule', []);

    if( is_array($schedule) && ! empty($schedule['start']))
      $parts[] = $this->icon('calendar') . ' ' . $this->esc($this->dateShort((string) $schedule['start']));

    if( $obj->body !== '' )
      $parts[] = $this->esc($this->excerpt($obj->body, 70));

    return implode(' <span class="sep">·</span> ', $parts);
  }
}
