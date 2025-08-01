<?php
/**
 * Edit form renderer for Activity type
 */
?>

<form id="activity-edit-form">
  <input type="hidden" name="type" value="Activity">
  
  <div class="mb-3">
    <label for="activity-name" class="form-label">Name *</label>
    <input type="text" class="form-control" id="activity-name" name="name" 
           value="<?= htmlspecialchars($data['name'] ?? '') ?>" required>
  </div>
  
  <div class="mb-3">
    <label for="activity-description" class="form-label">Description</label>
    <textarea class="form-control" id="activity-description" name="description" 
              rows="3"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
  </div>
  
  <div class="mb-3">
    <label for="activity-priority" class="form-label">Priority *</label>
    <select class="form-select" id="activity-priority" name="priority" required>
      <option value="">Select priority</option>
      <?php for( $i = 1; $i <= 5; $i++ ): ?>
        <option value="<?= $i ?>" 
          <?= (isset($data['priority']) && $data['priority'] == $i) ? 'selected' : '' ?>>
          <?= $i ?>
        </option>
      <?php endfor; ?>
    </select>
  </div>
  
  <div class="mb-3">
    <label for="activity-state" class="form-label">State *</label>
    <select class="form-select" id="activity-state" name="state" required>
      <option value="">Select state</option>
      <option value="new" 
        <?= (isset($data['state']) && $data['state'] == 'new') ? 'selected' : '' ?>>
        New
      </option>
      <option value="progress" 
        <?= (isset($data['state']) && $data['state'] == 'progress') ? 'selected' : '' ?>>
        In Progress
      </option>
      <option value="done" 
        <?= (isset($data['state']) && $data['state'] == 'done') ? 'selected' : '' ?>>
        Done
      </option>
    </select>
  </div>
  
  <div class="mb-3">
    <label for="activity-dueDate" class="form-label">Due Date</label>
    <input type="date" class="form-control" id="activity-dueDate" name="dueDate" 
           value="<?= htmlspecialchars($data['dueDate'] ?? '') ?>">
  </div>
</form>
