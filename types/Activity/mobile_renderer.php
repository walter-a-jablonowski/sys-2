<?php

/**
 * Activity cell: the status moves into its own column so the titles of a
 * list stay aligned on a narrow screen.
 */
class ActivityMobileRenderer extends \Sys\MobileRenderer
{
  public function render() : void
  {
    $obj    = $this->obj;
    $shown  = $obj->display();
    $status = (string) $shown->field('status', '');

    ?>
    <article class="cell cell-activity<?= $obj->isLink ? ' is-link' : '' ?>"
             data-path="<?= $this->esc($obj->path) ?>"
             data-type="<?= $this->esc($shown->type) ?>"
             data-container="<?= $shown->isFolder && ! $obj->isLink ? '1' : '0' ?>">
      <span class="cell-dot status-dot status-<?= $this->esc($status !== '' ? $status : 'none') ?>"></span>
      <div class="cell-body">
        <div class="cell-title"><?= $this->esc($obj->title) ?></div>
        <div class="cell-meta"><?= $this->meta() ?></div>
      </div>
      <?php if( $shown->isFolder ): ?>
        <span class="cell-chevron"><?= $this->icon('chevron') ?></span>
      <?php endif ?>
    </article>
    <?php
  }

  protected function meta() : string
  {
    $obj   = $this->obj->display();
    $parts = [];

    if( $obj->isFolder )
    {
      $n = $this->childCount();
      $parts[] = $n === 1 ? '1 entry' : "$n entries";
    }

    $schedule = $obj->field('schedule', []);

    if( is_array($schedule) && ! empty($schedule['start']))
      $parts[] = $this->esc($this->dateShort((string) $schedule['start']));

    return implode(' · ', $parts);
  }
}
