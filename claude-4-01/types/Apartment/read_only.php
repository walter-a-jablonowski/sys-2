<?php
// Apartment read-only renderer
// Variables available: $instance (array with instance data)

$name = htmlspecialchars($instance['name'] ?? 'Unnamed');
$description = $instance['description'] ?? '';
$state = $instance['state'] ?? 'new';
$result = htmlspecialchars($instance['result'] ?? '');
$filesNr = htmlspecialchars($instance['files_nr'] ?? '0000');
$url = $instance['url'] ?? '';
$created = formatDateLong($instance['time'] ?? '');

// State styling
$stateClass = "state-$state";
$stateText = ucfirst($state);
?>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <h5 class="card-title mb-0">
        <?php if( !empty($url) ): ?>
          <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" class="text-decoration-none">
            <?php echo $name; ?> 🔗
          </a>
        <?php else: ?>
          <?php echo $name; ?>
        <?php endif; ?>
      </h5>
      <div>
        <span class="badge <?php echo $stateClass; ?> me-2"><?php echo $stateText; ?></span>
        <span class="badge bg-secondary">#<?php echo $filesNr; ?></span>
      </div>
    </div>
    
    <?php if( !empty($description) ): ?>
      <p class="card-text"><?php echo nl2br(htmlspecialchars($description)); ?></p>
    <?php endif; ?>
    
    <?php if( !empty($result) ): ?>
      <div class="mb-3">
        <strong>Result:</strong> <?php echo $result; ?>
      </div>
    <?php endif; ?>
    
    <div class="row text-muted small">
      <div class="col-6">
        <strong>Created:</strong> <?php echo $created; ?>
      </div>
      <div class="col-6">
        <strong>Files Nr:</strong> #<?php echo $filesNr; ?>
      </div>
    </div>
    
    <!-- Camera button for image upload -->
    <div class="mt-3">
      <button class="btn btn-sm btn-outline-primary" onclick="uploadImage('<?php echo htmlspecialchars($instance['_path'] ?? ''); ?>')">
        📷 Add Photo
      </button>
    </div>
  </div>
</div>
