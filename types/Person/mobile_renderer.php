<?php

/**
 * Person cell: name and role only, the mail address does not fit a phone list.
 */
class PersonMobileRenderer extends \Sys\MobileRenderer
{
  protected function meta() : string
  {
    return $this->esc($this->obj->display()->field('role', ''));
  }
}
