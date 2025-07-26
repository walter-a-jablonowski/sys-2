<?php
// Apartment list cell renderer
// Variables available: $entry (array with entry data)

$name = htmlspecialchars($entry['name'] ?? 'Unnamed');
$state = $entry['state'] ?? 'new';
$url = $entry['url'] ?? '';
$date = formatDateLong($entry['time'] ?? '');
$filesNr = htmlspecialchars($entry['files_nr'] ?? '0000');

// State badge styling
$stateClass = "state-$state";
$stateText = ucfirst($state);
?>

<div>
  <!-- First line -->
  <div class="d-flex justify-content-between align-items-center mb-1">
    <div>
      <?php if( !empty($url) ): ?>
        <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" onclick="event.stopPropagation();" class="text-decoration-none">
          <?php echo $name; ?> 🔗
        </a>
      <?php else: ?>
        <?php echo $name; ?>
      <?php endif; ?>
    </div>
    <span class="badge <?php echo $stateClass; ?>"><?php echo $stateText; ?></span>
  </div>
  
  <!-- Second line -->
  <div class="d-flex justify-content-between align-items-center">
    <small class="text-muted"><?php echo $date; ?></small>
    <small class="text-muted">#<?php echo $filesNr; ?></small>
  </div>
</div>
