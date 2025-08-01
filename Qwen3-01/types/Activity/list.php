<?php
/**
 * List cell renderer for Activity type
 */
?>

<div class="d-flex justify-content-between align-items-center">
  <div>
    <?php if( isset($data['priority']) ): ?>
      <span class="priority-badge bg-primary">Priority <?= htmlspecialchars($data['priority']) ?></span>
    <?php endif; ?>
    <span class="ms-2"><?= htmlspecialchars($data['name'] ?? '') ?></span>
  </div>
  <div>
    <?php if( isset($data['state']) ): ?>
      <span class="status-badge 
        <?php 
          switch($data['state']) {
            case 'new': echo 'bg-secondary'; break;
            case 'progress': echo 'bg-warning'; break;
            case 'done': echo 'bg-success'; break;
            default: echo 'bg-secondary';
          }
        ?>">
        <?= htmlspecialchars(ucfirst($data['state'])) ?>
      </span>
    <?php endif; ?>
  </div>
</div>
