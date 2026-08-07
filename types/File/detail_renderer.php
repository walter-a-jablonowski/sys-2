<?php

/**
 * Resource editor: a preview of the file itself on top, the sidecar fields
 * below. Fields are written to "NAME.ext.md", the file stays untouched.
 */
class FileDetailRenderer extends \Sys\DetailRenderer
{
  private const IMAGES = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'bmp'];
  private const TEXT   = ['txt', 'md', 'csv', 'json', 'yml', 'yaml', 'log', 'ini', 'xml'];

  public function render() : void
  {
    $this->renderPreview();
    parent::render();
  }

  private function renderPreview() : void
  {
    $obj  = $this->obj;
    $abs  = $this->app->db->abs($obj->path);
    $url  = 'file.php?path=' . rawurlencode($obj->path);
    $ext  = strtolower(pathinfo($obj->title, PATHINFO_EXTENSION));
    $size = \Sys\FileSize::human($abs);

    ?>
    <div class="preview">
      <?php if( in_array($ext, self::IMAGES, true)): ?>
        <img class="preview-image" src="<?= $this->esc($url) ?>" alt="<?= $this->esc($obj->title) ?>">
      <?php elseif( $ext === 'pdf' ): ?>
        <iframe class="preview-frame" src="<?= $this->esc($url) ?>" title="<?= $this->esc($obj->title) ?>"></iframe>
      <?php elseif( in_array($ext, self::TEXT, true)): ?>
        <pre class="preview-text mono"><?= $this->esc($this->head($abs)) ?></pre>
      <?php else: ?>
        <div class="preview-none">
          <?= $this->icon('file') ?>
          <span>No preview for <b><?= $this->esc(strtoupper($ext)) ?></b> files</span>
        </div>
      <?php endif ?>

      <div class="preview-bar">
        <span class="mono"><?= $this->esc($obj->title) ?></span>
        <span class="sep">·</span>
        <span><?= $this->esc($size) ?></span>
        <a class="btn-quiet" href="<?= $this->esc($url) ?>" target="_blank" rel="noopener">Open</a>
        <a class="btn-quiet" href="<?= $this->esc($url) ?>&amp;download=1">Download</a>
      </div>
    </div>
    <?php
  }

  /**
   * First lines of a text file, enough to recognise it without loading it all.
   */
  private function head( string $abs ) : string
  {
    if( ! is_file($abs))
      return '';

    $text = (string) file_get_contents($abs, false, null, 0, 4000);

    return filesize($abs) > 4000 ? "$text\n…" : $text;
  }
}
