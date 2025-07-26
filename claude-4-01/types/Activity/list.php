<?php
// Activity list cell renderer
// Variables available: $entry (array with entry data)

$name = htmlspecialchars($entry['name'] ?? 'Unnamed');
$priority = (int)($entry['priority'] ?? 3);
$state = $entry['state'] ?? 'new';

// Priority badge
$priorityClass = "priority-$priority";
$priorityText = "P$priority";

// State badge  
$stateClass = "state-$state";
$stateText = ucfirst($state);
?>

<div class="d-flex justify-content-between align-items-center">
  <div class="d-flex align-items-center">
    <span class="badge <?php echo $priorityClass; ?> me-2"><?php echo $priorityText; ?></span>
    <span><?php echo $name; ?></span>
  </div>
  <span class="badge <?php echo $stateClass; ?>"><?php echo $stateText; ?></span>
</div>
