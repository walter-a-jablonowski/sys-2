<?php
// Info edit form renderer
// Variables available: $instance (array with instance data)

$name = htmlspecialchars($instance['name'] ?? '');
$description = htmlspecialchars($instance['description'] ?? '');
?>

<form id="editForm">
  <div class="mb-3">
    <label for="name" class="form-label">Name *</label>
    <input type="text" class="form-control" name="name" value="<?php echo $name; ?>" required>
  </div>
  
  <div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="5"><?php echo $description; ?></textarea>
  </div>
</form>
