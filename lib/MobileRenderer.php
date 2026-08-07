<?php

namespace Sys;

/**
 * Mobile cell. Laid out separately from the desktop card on purpose: on a
 * phone one tap target with fewer fields reads better than a shrunk card.
 */
class MobileRenderer extends Renderer
{
  public function render() : void
  {
    $obj   = $this->obj;
    $shown = $obj->display();

    ?>
    <article class="cell<?= $obj->isLink ? ' is-link' : '' ?>"
             data-path="<?= $this->esc($obj->path) ?>"
             data-type="<?= $this->esc($shown->type) ?>"
             data-container="<?= $shown->isFolder && ! $obj->isLink ? '1' : '0' ?>">
      <span class="cell-icon"><?= $this->icon($this->type->icon()) ?></span>
      <div class="cell-body">
        <div class="cell-title"><?= $this->esc($obj->title) ?><?= $obj->isLink ? $this->icon('link') : '' ?></div>
        <?php $meta = $this->meta() ?>
        <?php if( $meta !== '' ): ?>
          <div class="cell-meta"><?= $meta ?></div>
        <?php endif ?>
      </div>
      <?php if( $shown->isFolder ): ?>
        <span class="cell-chevron"><?= $this->icon('chevron') ?></span>
      <?php endif ?>
    </article>
    <?php
  }

  /**
   * One short line only, the phone has no room for more.
   */
  protected function meta() : string
  {
    $obj = $this->obj;

    if( $obj->isFolder )
    {
      $n = $this->childCount();
      return $n === 1 ? '1 entry' : "$n entries";
    }

    return $this->esc($this->excerpt($obj->body, 60));
  }
}
