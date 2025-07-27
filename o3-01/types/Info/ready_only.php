<?php /* Info read-only renderer */ ?>
<?php
  $cfg = app_config();
  $dataFile = is_dir($itemPath) ? "$itemPath/{$cfg['dataFileName']}.md" : $itemPath;
  $yaml = [];
  $body = '';
  if( file_exists($dataFile) ) [ $yaml, $body ] = parse_front_matter(file_get_contents($dataFile));
?>
<div class='p-2'>
  <h5><?= htmlspecialchars($yaml['name'] ?? basename($itemPath)) ?></h5>
  <p class='text-muted small'><?= htmlspecialchars($yaml['time'] ?? '') ?></p>
  <p><?= nl2br(htmlspecialchars($yaml['description'] ?? $body)) ?></p>
</div>
