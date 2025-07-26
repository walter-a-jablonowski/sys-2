<?php
// Activity edit form renderer
// Variables available: $instance (array with instance data)

$name = htmlspecialchars($instance['name'] ?? '');
$description = htmlspecialchars($instance['description'] ?? '');
$priority = (int)($instance['priority'] ?? 3);
$state = $instance['state'] ?? 'new';
$dueDate = htmlspecialchars($instance['dueDate'] ?? '');
?>

<form id="editForm">
  <div class="mb-3">
    <label for="name" class="form-label">Name *</label>
    <input type="text" class="form-control" name="name" value="<?php echo $name; ?>" required>
  </div>
  
  <div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="3"><?php echo $description; ?></textarea>
  </div>
  
  <div class="row">
    <div class="col-md-6">
      <div class="mb-3">
        <label for="priority" class="form-label">Priority *</label>
        <select class="form-select" name="priority" required>
          <option value="1" <?php echo $priority == 1 ? 'selected' : ''; ?>>1 - Highest</option>
          <option value="2" <?php echo $priority == 2 ? 'selected' : ''; ?>>2 - High</option>
          <option value="3" <?php echo $priority == 3 ? 'selected' : ''; ?>>3 - Medium</option>
          <option value="4" <?php echo $priority == 4 ? 'selected' : ''; ?>>4 - Low</option>
          <option value="5" <?php echo $priority == 5 ? 'selected' : ''; ?>>5 - Lowest</option>
        </select>
      </div>
    </div>
    
    <div class="col-md-6">
      <div class="mb-3">
        <label for="state" class="form-label">State *</label>
        <select class="form-select" name="state" required>
          <option value="new" <?php echo $state == 'new' ? 'selected' : ''; ?>>New</option>
          <option value="progress" <?php echo $state == 'progress' ? 'selected' : ''; ?>>In Progress</option>
          <option value="done" <?php echo $state == 'done' ? 'selected' : ''; ?>>Done</option>
        </select>
      </div>
    </div>
  </div>
  
  <div class="mb-3">
    <label for="dueDate" class="form-label">Due Date</label>
    <input type="date" class="form-control" name="dueDate" value="<?php echo $dueDate; ?>">
    <div class="form-text">Optional - format: YYYY-MM-DD</div>
  </div>
</form>
