<?php /* $d, $is_new, $type_id */ ?>
<form id="type-edit-form">
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars((string)($d['name'] ?? '')) ?>" required>
  </div>
  <div class="row g-2">
    <div class="col-6">
      <label class="form-label">Priority</label>
      <select class="form-select" name="priority">
        <?php for( $i = 1; $i <= 5; $i++ ) : ?>
          <option value="<?= $i ?>" <?php if( (string)($d['priority'] ?? '3') === (string)$i ) : ?>selected<?php endif ?>><?= $i ?></option>
        <?php endfor ?>
      </select>
    </div>
    <div class="col-6">
      <label class="form-label">State</label>
      <select class="form-select" name="state">
        <?php $states = ['new','progress','done']; foreach( $states as $s ) : ?>
          <option value="<?= $s ?>" <?php if( (string)($d['state'] ?? 'new') === $s ) : ?>selected<?php endif ?>><?= $s ?></option>
        <?php endforeach ?>
      </select>
    </div>
  </div>
  <div class="mt-3">
    <label class="form-label">Due date (YYYY-MM-DD HH:MM:SS)</label>
    <input type="text" class="form-control" name="dueDate" value="<?= htmlspecialchars((string)($d['dueDate'] ?? '')) ?>" placeholder="2025-12-31 12:00:00">
  </div>
  <div class="mt-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" rows="5" name="description"><?= htmlspecialchars((string)($d['description'] ?? '')) ?></textarea>
  </div>
</form>
