<div class="d-flex justify-content-between align-items-center">
  <div>
    <?php 
    // Extract date from name (format: YYMMDD - Name)
    $name = $instanceData['name'] ?? '';
    $dateMatch = [];
    if( preg_match('/^(\d{6})\s*-\s*(.+)$/', $name, $dateMatch) ) {
      $dateStr = $dateMatch[1];
      $displayName = $dateMatch[2];
      // Convert YYMMDD to MM-DD format
      $month = substr($dateStr, 2, 2);
      $day = substr($dateStr, 4, 2);
      $displayDate = "$month-$day";
    } else {
      $displayDate = substr($instanceData['time'] ?? '', 5, 5); // MM-DD from time
      $displayName = $name;
    }
    ?>
    <small class="text-muted me-2"><?= htmlspecialchars($displayDate) ?></small>
  </div>
  <div>
    <strong><?= htmlspecialchars($displayName) ?></strong>
  </div>
</div>
