<?php
// Info read-only renderer
// Variables available: $instance (array with instance data)

$name = htmlspecialchars($instance['name'] ?? 'Unnamed');
$description = $instance['description'] ?? '';
$created = formatDateLong($instance['time'] ?? '');
?>

<div class="card">
  <div class="card-body">
    <h5 class="card-title"><?php echo $name; ?></h5>
    
    <?php if( !empty($description) ): ?>
      <div class="card-text">
        <?php echo nl2br(htmlspecialchars($description)); ?>
      </div>
    <?php endif; ?>
    
    <div class="mt-3">
      <small class="text-muted">Created: <?php echo $created; ?></small>
    </div>
  </div>
</div>
