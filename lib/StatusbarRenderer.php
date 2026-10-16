<?php

namespace Sys;

/**
 * Type specific facts in the foot band, next to the save state. Empty unless
 * the viewer returns parts(). A part may also be a tiny button, made with
 * actionButton(), where an action belongs right next to a fact.
 */
class StatusbarRenderer extends Renderer
{
  public function render() : void
  {
    $parts = array_filter($this->parts());

    if( ! $parts )
      return;

    ?>
    <div class="statusbar"><?= implode(' <span class="sep">·</span> ', $parts) ?></div>
    <?php
  }

  /**
   * Html snippets, shown in a row with a dot between them. Viewers override this.
   */
  protected function parts() : array
  {
    return [];
  }
}
