<form id="editForm">
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($instanceData['name'] ?? '') ?>" required>
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
