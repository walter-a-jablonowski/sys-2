<?php
/**
 * Read only renderer for Apartment type
 */
?>

<div class="apartment-details">
  <h3>
    <?php if( isset($data['url']) && !empty($data['url']) ): ?>
      <a href="<?= htmlspecialchars($data['url']) ?>" target="_blank">
        <?= htmlspecialchars($data['name'] ?? '') ?>
      </a>
    <?php else: ?>
      <?= htmlspecialchars($data['name'] ?? '') ?>
    <?php endif; ?>
  </h3>
  
  <?php if( isset($data['description']) && !empty($data['description']) ): ?>
    <div class="description mb-3">
      <?= nl2br(htmlspecialchars($data['description'])) ?>
    </div>
  <?php endif; ?>
  
  <div class="row">
    <?php if( isset($data['state']) ): ?>
      <div class="col-md-6 mb-2">
        <strong>Status:</strong> 
        <span class="status-badge 
          <?php 
            switch($data['state']) {
              case 'new': echo 'bg-secondary'; break;
              case 'current': echo 'bg-primary'; break;
              case 'maybe': echo 'bg-warning'; break;
              case 'done': echo 'bg-success'; break;
              default: echo 'bg-secondary';
            }
          ?>">
          <?= htmlspecialchars(ucfirst($data['state'])) ?>
        </span>
      </div>
    <?php endif; ?>
    
    <?php if( isset($data['result']) && !empty($data['result']) ): ?>
      <div class="col-md-6 mb-2">
        <strong>Result:</strong> 
        <?= htmlspecialchars($data['result']) ?>
      </div>
    <?php endif; ?>
    
    <?php if( isset($data['files_nr']) && !empty($data['files_nr']) ): ?>
      <div class="col-md-6 mb-2">
        <strong>File Number:</strong> 
        #<?= htmlspecialchars($data['files_nr']) ?>
      </div>
    <?php endif; ?>
    
    <?php if( isset($data['url']) && !empty($data['url']) ): ?>
      <div class="col-md-6 mb-2">
        <strong>URL:</strong> 
        <a href="<?= htmlspecialchars($data['url']) ?>" target="_blank">
          <?= htmlspecialchars($data['url']) ?>
        </a>
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
