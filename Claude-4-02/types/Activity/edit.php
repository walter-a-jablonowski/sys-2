<form id="editForm">
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($instanceData['name'] ?? '') ?>" required>
  </div>
  
  <div class="mb-3">
    <label class="form-label">Priority</label>
    <select class="form-select" name="priority" required>
      <?php for( $i = 1; $i <= 5; $i++ ): ?>
        <option value="<?= $i ?>" <?= ($instanceData['priority'] ?? 1) == $i ? 'selected' : '' ?>><?= $i ?></option>
      <?php endfor; ?>
    </select>
  </div>
  
  <div class="mb-3">
    <label class="form-label">State</label>
    <select class="form-select" name="state" required>
      <option value="new" <?= ($instanceData['state'] ?? 'new') === 'new' ? 'selected' : '' ?>>New</option>
      <option value="progress" <?= ($instanceData['state'] ?? 'new') === 'progress' ? 'selected' : '' ?>>In Progress</option>
      <option value="done" <?= ($instanceData['state'] ?? 'new') === 'done' ? 'selected' : '' ?>>Done</option>
    </select>
  </div>
  
  <div class="mb-3">
    <label class="form-label">Due Date (optional)</label>
    <input type="text" class="form-control" name="dueDate" value="<?= htmlspecialchars($instanceData['dueDate'] ?? '') ?>" pattern="\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}" placeholder="YYYY-MM-DD HH:MM:SS">
  </div>
  
  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($instanceData['description'] ?? '') ?></textarea>
  </div>
  
  <div class="mb-3">
    <label class="form-label">Time</label>
    <input type="text" class="form-control" name="time" value="<?= htmlspecialchars($instanceData['time'] ?? '') ?>" pattern="\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}" placeholder="YYYY-MM-DD HH:MM:SS">
  </div>
  
  <input type="hidden" name="id" value="<?= htmlspecialchars($instanceData['id'] ?? '') ?>">
  <input type="hidden" name="type" value="<?= htmlspecialchars($instanceData['type'] ?? '') ?>">
</form>
