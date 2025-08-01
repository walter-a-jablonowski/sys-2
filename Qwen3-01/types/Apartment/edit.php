<?php
/**
 * Edit form renderer for Apartment type
 */
?>

<form id="apartment-edit-form">
  <input type="hidden" name="type" value="Apartment">
  
  <div class="mb-3">
    <label for="apartment-name" class="form-label">Name *</label>
    <input type="text" class="form-control" id="apartment-name" name="name" 
           value="<?= htmlspecialchars($data['name'] ?? '') ?>" required>
  </div>
  
  <div class="mb-3">
    <label for="apartment-description" class="form-label">Description</label>
    <textarea class="form-control" id="apartment-description" name="description" 
              rows="3"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
  </div>
  
  <div class="mb-3">
    <label for="apartment-state" class="form-label">Status *</label>
    <select class="form-select" id="apartment-state" name="state" required>
      <option value="">Select status</option>
      <option value="new" 
        <?= (isset($data['state']) && $data['state'] == 'new') ? 'selected' : '' ?>>
        New
      </option>
      <option value="current" 
        <?= (isset($data['state']) && $data['state'] == 'current') ? 'selected' : '' ?>>
        Current
      </option>
      <option value="maybe" 
        <?= (isset($data['state']) && $data['state'] == 'maybe') ? 'selected' : '' ?>>
        Maybe
      </option>
      <option value="done" 
        <?= (isset($data['state']) && $data['state'] == 'done') ? 'selected' : '' ?>>
        Done
      </option>
    </select>
  </div>
  
  <div class="mb-3">
    <label for="apartment-result" class="form-label">Result</label>
    <input type="text" class="form-control" id="apartment-result" name="result" 
           value="<?= htmlspecialchars($data['result'] ?? '') ?>">
  </div>
  
  <div class="mb-3">
    <label for="apartment-url" class="form-label">URL</label>
    <input type="url" class="form-control" id="apartment-url" name="url" 
           value="<?= htmlspecialchars($data['url'] ?? '') ?>">
  </div>
</form>
