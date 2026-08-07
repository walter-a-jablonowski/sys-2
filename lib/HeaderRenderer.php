<?php

namespace Sys;

/**
 * Content of the app header segment above the editor (NAME_2 in the plan).
 * The band itself is drawn by the shell, only the content is type specific.
 */
class HeaderRenderer extends Renderer
{
  public function render() : void
  {
    ?>
    <span class="head-icon"><?= $this->icon($this->type->icon()) ?></span>
    <span class="head-title"><?= $this->esc($this->obj->title) ?></span>
    <span class="head-type"><?= $this->esc($this->type->label()) ?></span>
    <?php
  }
}
