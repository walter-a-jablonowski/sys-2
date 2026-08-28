<?php

/**
 * Search overlay. The one place in the app that reaches across the whole
 * hierarchy at once, so it is the only overlay that covers the columns.
 *
 * @var string $icons
 */

?>
<div class="palette" data-role="palette" hidden>
  <div class="palette-box" role="dialog" aria-modal="true" aria-label="Search">

    <label class="palette-field">
      <svg class="icon" aria-hidden="true"><use href="<?= $icons ?>#search"></use></svg>
      <input type="text" class="palette-input" data-role="paletteInput" autocomplete="off"
             placeholder="Search the whole tree" aria-label="Search">
      <span class="palette-key">Esc</span>
    </label>

    <div class="palette-hits" data-role="paletteHits"></div>

  </div>
</div>
