<?php

namespace Sys;

/**
 * Desktop card. The skeleton is shared by every type so the list stays one
 * consistent surface, a type only fills the parts below.
 */
class ListRenderer extends Renderer
{
  public function render() : void
  {
    $obj    = $this->obj;
    $shown  = $obj->display();
    $meta   = $this->meta();
    $badges = $this->badges();

    ?>
    <article class="card<?= $obj->isLink ? ' is-link' : '' ?>"
             data-path="<?= $this->esc($obj->path) ?>"
             data-type="<?= $this->esc($shown->type) ?>"
             data-container="<?= $shown->isFolder && ! $obj->isLink ? '1' : '0' ?>">
      <span class="card-icon"><?= $this->icon($this->type->icon()) ?></span>
      <div class="card-body">
        <div class="card-line">
          <span class="card-title"><?= $this->esc($obj->title) ?></span>
          <?php if( $obj->isLink ): ?>
            <span class="card-linkmark" title="Link"><?= $this->icon('link') ?></span>
          <?php endif ?>
          <?php if( $badges !== '' ): ?>
            <span class="card-badges"><?= $badges ?></span>
          <?php endif ?>
        </div>
        <?php if( $meta !== '' ): ?>
          <div class="card-meta"><?= $meta ?></div>
        <?php endif ?>
      </div>
      <div class="card-tail">
        <button class="card-action" data-action="menu" title="Actions" aria-label="Actions"><?= $this->icon('more') ?></button>
        <?php if( $shown->isFolder ): ?>
          <span class="card-chevron"><?= $this->icon('chevron') ?></span>
        <?php endif ?>
      </div>
    </article>
    <?php
  }

  /**
   * Second line of the card. Types override this.
   */
  protected function meta() : string
  {
    $obj   = $this->obj;
    $parts = [];

    if( $obj->isFolder )
    {
      $n = $this->childCount();
      $parts[] = $n === 1 ? '1 entry' : "$n entries";
    }

    if( $obj->body !== '' )
      $parts[] = $this->esc($this->excerpt($obj->body));

    return implode(' <span class="sep">·</span> ', $parts);
  }

  /**
   * Small markers at the right of the title. Types override this.
   */
  protected function badges() : string
  {
    return '';
  }
}
