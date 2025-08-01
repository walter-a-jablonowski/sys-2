<?php
/**
 * List cell renderer for Info type
 */
?>

<div class="d-flex justify-content-between align-items-center">
  <div>
    <?php if( isset($data['time']) ): 
      // Extract MM-DD from YYYY-MM-DD format
      $dateParts = explode('-', $data['time']);
      if( count($dateParts) >= 3 ) {
        $displayDate = $dateParts[1] . '-' . $dateParts[2];
        echo '<span class="text-muted small">' . htmlspecialchars($displayDate) . '</span>';
      }
    endif; ?>
  </div>
  <div>
    <span><?= htmlspecialchars($data['name'] ?? '') ?></span>
  </div>
</div>
