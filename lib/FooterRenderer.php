<?php

namespace Sys;

/**
 * Content of the footer segment below the editor (FOO_2 in the plan).
 * Empty unless a type fills it, the save state is added by the shell.
 */
class FooterRenderer extends Renderer
{
  public function render() : void
  {
    $parts = [];

    if( $this->obj->created !== '' )
      $parts[] = 'created ' . $this->dateShort($this->obj->created);

    if( $this->obj->modified !== '' )
      $parts[] = 'changed ' . $this->dateShort($this->obj->modified);

    if( $this->obj->id !== '' )
      $parts[] = '<code class="mono">' . $this->esc($this->obj->id) . '</code>';

    echo implode(' <span class="sep">·</span> ', $parts);
  }
}
