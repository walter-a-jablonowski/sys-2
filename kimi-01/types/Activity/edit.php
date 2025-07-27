<?php
// Edit form for Activity type
?>
<form id="editForm">
  <input type="hidden" name="id" value="<?= htmlspecialchars($entry['id']) ?>">
  <input type="hidden" name="path" value="<?= htmlspecialchars($entry['path']) ?>">
  <input type="hidden" name="type" value="<?= htmlspecialchars($entry['type']) ?>">
  
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($entry['name']) ?>">
  </div>
  
  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="5"><?= htmlspecialchars($entry['description']) ?></textarea>
  </div>
  
  <div class="mb-3">
    <label class="form-label">Priority</label>
    <select class="form-select" name="priority">
      <option value="low" <?= ($entry['data']['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
      <option value="medium" <?= ($entry['data']['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
      <option value="high" <?= ($entry['data']['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
    </select>
  </div>
  
  <div class="mb-3">
    <label class="form-label">State</label>
    <select class="form-select" name="state">
      <option value="open" <?= ($entry['data']['state'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
      <option value="in_progress" <?= ($entry['data']['state'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
      <option value="done" <?= ($entry['data']['state'] ?? '') === 'done' ? 'selected' : '' ?>>Done</option>
      <option value="cancelled" <?= ($entry['data']['state'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>
  </div>
  
  <div class="mb-3">
    <label class="form-label">Due Date</label>
    <input type="datetime-local" class="form-control" name="dueDate" 
           value="<?= str_replace(' ', 'T', $entry['data']['dueDate'] ?? '') ?>">
  </div>
  
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" onclick="saveEntry()">Save</button>
  </div>
</form>
