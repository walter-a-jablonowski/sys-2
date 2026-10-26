<?php

/**
 * Only reached when a link has no target any more: every intact link is drawn
 * by the renderer of the object it points at.
 */
class LinkDefaultListRenderer extends \Sys\ListRenderer
{
  protected function badges() : string
  {
    return '<span class="status status-broken">broken link</span>';
  }

  protected function meta() : string
  {
    return 'target <code class="mono">' . $this->esc($this->obj->target ?: $this->obj->altTarget) . '</code> does not exist';
  }
}
