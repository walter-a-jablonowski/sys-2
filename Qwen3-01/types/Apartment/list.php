<?php
/**
 * List cell renderer for Apartment type
 */
?>

<div>
  <!-- First line -->
  <div class="d-flex justify-content-between align-items-center mb-1">
    <div>
      <?php if( isset($data['url']) && !empty($data['url']) ): ?>
        <a href="<?= htmlspecialchars($data['url']) ?>" target="_blank">
          <?= htmlspecialchars($data['name'] ?? '') ?>
        </a>
      <?php else: ?>
        <?= htmlspecialchars($data['name'] ?? '') ?>
      <?php endif; ?>
    </div>
    <div>
      <?php if( isset($data['state']) ): ?>
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
      <?php endif; ?>
    </div>
  </div>
  
  <!-- Second line -->
  <div class="d-flex justify-content-between align-items-center small text-muted">
    <div>
      <?php if( isset($data['time']) ): 
        // Extract YYYY-MM-DD from YYYY-MM-DD HH:MM:SS format
        $dateParts = explode(' ', $data['time']);
        if( count($dateParts) > 0 ) {
          echo htmlspecialchars($dateParts[0]);
        }
      endif; ?>
    </div>
    <div>
      <?php if( isset($data['files_nr']) && !empty($data['files_nr']) ): ?>
        #<?= htmlspecialchars($data['files_nr']) ?>
      <?php endif; ?>
    </div>
  </div>
</div>
