<?php

namespace Sys;

/**
 * Buttons a viewer puts into the header, next to the object's name. Empty
 * unless the viewer returns buttons(). On a phone they fold into a dropdown,
 * both are made from the same entries so they never disagree.
 */
class ToolbarRenderer extends Renderer
{
  public function render() : void
  {
    $buttons = array_map([$this, 'action'], $this->buttons());

    if( ! $buttons )
      return;

    ?>
    <div class="toolbar">
      <?php foreach( $buttons as $button ): ?>
        <?= $this->actionButton($button, 'btn-quiet toolbar-btn') ?>
      <?php endforeach ?>
      <button type="button" class="btn-quiet toolbar-fold" data-action="toolbarMenu"
              data-entries="<?= $this->esc(json_encode($buttons)) ?>">
        Actions <?= $this->icon('chevron-down') ?>
      </button>
    </div>
    <?php
  }

  /**
   * Action entries, see Renderer::actionButton(). Viewers override this.
   */
  protected function buttons() : array
  {
    return [];
  }
}
