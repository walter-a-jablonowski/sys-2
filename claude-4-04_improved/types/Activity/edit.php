<?php
// Activity edit form renderer
$isEdit = isset($entry);
$priority = $isEdit ? ($entry['priority'] ?? 1) : 1;
$state = $isEdit ? ($entry['state'] ?? 'new') : 'new';
$dueDate = $isEdit ? ($entry['dueDate'] ?? '') : '';
$name = $isEdit ? ($entry['name'] ?? '') : '';
$description = $isEdit ? ($entry['description'] ?? '') : '';
$path = $isEdit ? ($entry['_path'] ?? '') : '';
?>

<form id="<?= $isEdit ? 'editEntryForm' : 'addEntryForm' ?>">
  <div class="mb-3">
    <label for="name" class="form-label">Name *</label>
    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
  </div>
  
  <div class="mb-3">
    <label for="priority" class="form-label">Priority *</label>
    <select class="form-select" id="priority" name="priority" required>
      <option value="1" <?= $priority == 1 ? 'selected' : '' ?>>1 - Highest</option>
      <option value="2" <?= $priority == 2 ? 'selected' : '' ?>>2 - High</option>
      <option value="3" <?= $priority == 3 ? 'selected' : '' ?>>3 - Medium</option>
      <option value="4" <?= $priority == 4 ? 'selected' : '' ?>>4 - Low</option>
      <option value="5" <?= $priority == 5 ? 'selected' : '' ?>>5 - Lowest</option>
    </select>
  </div>
  
  <div class="mb-3">
    <label for="state" class="form-label">State *</label>
    <select class="form-select" id="state" name="state" required>
      <option value="new" <?= $state == 'new' ? 'selected' : '' ?>>New</option>
      <option value="progress" <?= $state == 'progress' ? 'selected' : '' ?>>In Progress</option>
      <option value="done" <?= $state == 'done' ? 'selected' : '' ?>>Done</option>
    </select>
  </div>
  
  <div class="mb-3">
    <label for="dueDate" class="form-label">Due Date</label>
    <input type="date" class="form-control" id="dueDate" name="dueDate" value="<?= htmlspecialchars($dueDate) ?>">
  </div>
  
  <div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
  </div>
  
  <input type="hidden" name="type" value="Activity">
  
  <div class="d-flex justify-content-end">
    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" onclick="<?= $isEdit ? "submitEditForm('Activity', '$path')" : "submitAddForm('Activity')" ?>">
      <?= $isEdit ? 'Update' : 'Create' ?>
    </button>
  </div>
</form>
