<?php /* $d, $is_new, $type_id */ ?>
<form id="type-edit-form">
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars((string)($d['name'] ?? '')) ?>" required>
  </div>
  <div class="mt-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" rows="5" name="description"><?= htmlspecialchars((string)($d['description'] ?? '')) ?></textarea>
  </div>
</form>
