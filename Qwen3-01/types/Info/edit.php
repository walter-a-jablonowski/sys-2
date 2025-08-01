<?php
/**
 * Edit form renderer for Info type
 */
?>

<form id="info-edit-form">
  <input type="hidden" name="type" value="Info">
  
  <div class="mb-3">
    <label for="info-name" class="form-label">Name *</label>
    <input type="text" class="form-control" id="info-name" name="name" 
           value="<?= htmlspecialchars($data['name'] ?? '') ?>" required>
  </div>
  
  <div class="mb-3">
    <label for="info-description" class="form-label">Description</label>
    <textarea class="form-control" id="info-description" name="description" 
              rows="3"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
  </div>
</form>
