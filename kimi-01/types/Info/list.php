<?php
// List cell renderer for Info type
?>
<div>
  <h6 class="mb-1"><?= htmlspecialchars($entry['name']) ?></h6>
  <small class="text-muted d-block"><?= htmlspecialchars($entry['type']) ?></small>
  <small class="text-muted d-block"><?= htmlspecialchars($entry['time']) ?></small>
  <?php if (!empty($entry['description'])): ?>
    <small class="text-muted d-block text-truncate" style="max-width: 200px;">
      <?= htmlspecialchars($entry['description']) ?>
    </small>
  <?php endif; ?>
</div>
