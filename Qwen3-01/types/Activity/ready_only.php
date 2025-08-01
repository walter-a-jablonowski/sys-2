<?php
/**
 * Read only renderer for Activity type
 */
?>

<div class="activity-details">
  <h3><?= htmlspecialchars($data['name'] ?? '') ?></h3>
  
  <?php if( isset($data['description']) && !empty($data['description']) ): ?>
    <div class="description mb-3">
      <?= nl2br(htmlspecialchars($data['description'])) ?>
    </div>
  <?php endif; ?>
  
  <div class="row">
    <?php if( isset($data['priority']) ): ?>
      <div class="col-md-6 mb-2">
        <strong>Priority:</strong> 
        <span class="priority-badge bg-primary"><?= htmlspecialchars($data['priority']) ?></span>
      </div>
    <?php endif; ?>
    
    <?php if( isset($data['state']) ): ?>
      <div class="col-md-6 mb-2">
        <strong>Status:</strong> 
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
      </div>
    <?php endif; ?>
    
    <?php if( isset($data['dueDate']) && !empty($data['dueDate']) ): ?>
      <div class="col-md-6 mb-2">
        <strong>Due Date:</strong> 
        <?= htmlspecialchars($data['dueDate']) ?>
      </div>
    <?php endif; ?>
    
    <?php if( isset($data['time']) && !empty($data['time']) ): ?>
      <div class="col-md-6 mb-2">
        <strong>Created:</strong> 
        <?= htmlspecialchars($data['time']) ?>
      </div>
    <?php endif; ?>
  </div>
</div>
