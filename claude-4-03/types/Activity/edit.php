<?php
// Activity edit renderer for modal

$name = $entry['name'] ?? '';
$description = $entry['description'] ?? '';
$priority = $entry['priority'] ?? 3;
$state = $entry['state'] ?? 'new';
$dueDate = $entry['dueDate'] ?? '';
?>

<form id="editActivityForm">
  <input type="hidden" id="editEntryPath" value="<?= htmlspecialchars($entry['path']) ?>">
  <input type="hidden" id="editEntryType" value="Activity">
  
  <div class="mb-3">
    <label for="editActivityName" class="form-label">Name</label>
    <input type="text" class="form-control" id="editActivityName" value="<?= htmlspecialchars($name) ?>" required>
  </div>
  
  <div class="row mb-3">
    <div class="col-6">
      <label for="editActivityPriority" class="form-label">Priority</label>
      <select class="form-select" id="editActivityPriority" required>
        <?php for( $i = 1; $i <= 5; $i++ ): ?>
        <option value="<?= $i ?>" <?= $priority == $i ? 'selected' : '' ?>><?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-6">
      <label for="editActivityState" class="form-label">State</label>
      <select class="form-select" id="editActivityState" required>
        <option value="new" <?= $state === 'new' ? 'selected' : '' ?>>New</option>
        <option value="progress" <?= $state === 'progress' ? 'selected' : '' ?>>In Progress</option>
        <option value="done" <?= $state === 'done' ? 'selected' : '' ?>>Done</option>
      </select>
    </div>
  </div>
  
  <div class="mb-3">
    <label for="editActivityDueDate" class="form-label">Due Date (optional)</label>
    <input type="datetime-local" class="form-control" id="editActivityDueDate" value="<?= $dueDate ? date('Y-m-d\TH:i', strtotime($dueDate)) : '' ?>">
  </div>
  
  <div class="mb-3">
    <label for="editActivityDescription" class="form-label">Description</label>
    <textarea class="form-control" id="editActivityDescription" rows="3"><?= htmlspecialchars($description) ?></textarea>
  </div>
</form>
