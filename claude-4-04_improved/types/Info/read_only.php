<?php
// Info read-only renderer
?>

<div class="type-info">
  <h5><?= htmlspecialchars($entry['name']) ?></h5>
  
  <p class="mb-2">
    <strong>Created:</strong> <?= htmlspecialchars($entry['time']) ?>
  </p>
  
  <?php if( isset($entry['description']) && $entry['description'] ): ?>
  <div class="mt-3">
    <strong>Description:</strong>
    <div class="mt-2">
      <?= nl2br(htmlspecialchars($entry['description'])) ?>
    </div>
  </div>
  <?php endif; ?>
</div>
