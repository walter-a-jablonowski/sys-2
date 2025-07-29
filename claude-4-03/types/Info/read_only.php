<?php
// Info read-only renderer using PHP's alternative syntax

$name = $entry['name'] ?? 'Untitled';
$time = $entry['time'] ?? '';
$description = $entry['description'] ?? '';
?>

<div class="info-readonly">
  <h5><?= htmlspecialchars($name) ?></h5>
  
  <div class="mb-3">
    <small class="text-muted">Created:</small><br>
    <span><?= htmlspecialchars($time) ?></span>
  </div>
  
  <?php if( $description ): ?>
  <div class="mb-3">
    <small class="text-muted">Description:</small><br>
    <p><?= nl2br(htmlspecialchars($description)) ?></p>
  </div>
  <?php endif; ?>
</div>
