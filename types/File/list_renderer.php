<?php

/**
 * Resource card: extension and size, the two things that tell files apart.
 */
class FileListRenderer extends \Sys\ListRenderer
{
  protected function badges() : string
  {
    $ext = strtoupper(pathinfo($this->obj->title, PATHINFO_EXTENSION));

    return $ext === '' ? '' : '<span class="status">' . $this->esc($ext) . '</span>';
  }

  protected function meta() : string
  {
    $parts = [\Sys\FileSize::human($this->app->db->abs($this->obj->path))];

    if( $this->obj->body !== '' )
      $parts[] = $this->esc($this->excerpt($this->obj->body, 60));

    return implode(' <span class="sep">·</span> ', array_filter($parts));
  }
}
