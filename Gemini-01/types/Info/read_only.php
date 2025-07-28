<?php
// types/Info/read_only.php

/**
 * @var array $entry_data
 */
?>

<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0"><?= htmlspecialchars($entry_data['name']) ?></h5>
  </div>
  <div class="card-body">
    <p class="card-text"><?= nl2br(htmlspecialchars($entry_data['description'])) ?></p>
  </div>
  <div class="card-footer text-muted">
    <small>Type: <?= htmlspecialchars($entry_data['type']) ?></small>
  </div>
</div>

<div class="mt-4">
  <h5>Sub-Items</h5>
  <hr>
  <ul class="nav nav-tabs" id="sub-items-tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="sub-list-tab" data-bs-toggle="tab" data-bs-target="#sub-list-content" type="button" role="tab" aria-controls="sub-list-content" aria-selected="true">List</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="sub-resources-tab" data-bs-toggle="tab" data-bs-target="#sub-resources-content" type="button" role="tab" aria-controls="sub-resources-content" aria-selected="false">Resources</button>
    </li>
  </ul>
  <div class="tab-content" id="sub-items-tabs-content">
    <div class="tab-pane fade show active" id="sub-list-content" role="tabpanel" aria-labelledby="sub-list-tab">
      <div class="list-group mt-2">
        <?php if (empty($typed_entries)) : ?>
          <div class="alert alert-info mt-2">No sub-items found.</div>
        <?php else : ?>
          <?php foreach ($typed_entries as $entry) : ?>
            <?php
            global $system;
            $renderer_path = $system->getRendererPath($entry['type'], 'list.php');
            if ($renderer_path) {
              echo '<div class="list-group-item list-group-item-action" data-path="' . htmlspecialchars($entry['path']) . '">';
              $entry_data = $entry;
              include $renderer_path;
              echo '</div>';
            } else {
              echo '<div class="list-group-item">' . htmlspecialchars($entry['name']) . ' (Unknown Type)</div>';
            }
            ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="tab-pane fade" id="sub-resources-content" role="tabpanel" aria-labelledby="sub-resources-tab">
      <div class="list-group mt-2">
        <?php if (empty($resource_folders)) : ?>
          <div class="alert alert-info mt-2">No resources found.</div>
        <?php else : ?>
          <?php foreach ($resource_folders as $item) : ?>
            <div class="list-group-item list-group-item-action" data-path="<?= htmlspecialchars($item['path']) ?>">
              <i class="bi bi-folder"></i> <?= htmlspecialchars($item['name']) ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
