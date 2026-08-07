<?php

/**
 * Resource cell: name and size, nothing else fits.
 */
class FileMobileRenderer extends \Sys\MobileRenderer
{
  protected function meta() : string
  {
    return \Sys\FileSize::human($this->app->db->abs($this->obj->path));
  }
}
