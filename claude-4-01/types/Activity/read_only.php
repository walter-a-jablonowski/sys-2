<?php
// Activity read-only renderer
// Variables available: $instance (array with instance data)

$name = htmlspecialchars($instance['name'] ?? 'Unnamed');
$description = $instance['description'] ?? '';
$priority = (int)($instance['priority'] ?? 3);
$state = $instance['state'] ?? 'new';
$dueDate = $instance['dueDate'] ?? '';
$created = formatDateLong($instance['time'] ?? '');

// Priority and state styling
$priorityClass = "priority-$priority";
$stateClass = "state-$state";
$stateText = ucfirst($state);
?>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <h5 class="card-title mb-0"><?php echo $name; ?></h5>
      <div>
        <span class="badge <?php echo $priorityClass; ?> me-2">Priority <?php echo $priority; ?></span>
        <span class="badge <?php echo $stateClass; ?>"><?php echo $stateText; ?></span>
      </div>
    </div>
    
    <?php if( !empty($description) ): ?>
      <p class="card-text"><?php echo nl2br(htmlspecialchars($description)); ?></p>
    <?php endif; ?>
    
    <div class="row text-muted small">
      <div class="col-6">
        <strong>Created:</strong> <?php echo $created; ?>
      </div>
      <?php if( !empty($dueDate) ): ?>
        <div class="col-6">
          <strong>Due Date:</strong> <?php echo htmlspecialchars($dueDate); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
