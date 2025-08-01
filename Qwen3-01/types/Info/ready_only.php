<?php
/**
 * Read only renderer for Info type
 */
?>

<div class="info-details">
  <h3><?= htmlspecialchars($data['name'] ?? '') ?></h3>
  
  <?php if( isset($data['description']) && !empty($data['description']) ): ?>
    <div class="description mb-3">
      <?= nl2br(htmlspecialchars($data['description'])) ?>
    </div>
  <?php endif; ?>
  
  <?php if( isset($data['time']) && !empty($data['time']) ): ?>
    <div class="text-muted small mb-2">
      <strong>Created:</strong> 
      <?= htmlspecialchars($data['time']) ?>
    </div>
  <?php endif; ?>
</div>
