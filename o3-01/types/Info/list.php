<?php /* Info list renderer */ ?>
<?php
  $cfg = app_config();
  $dataFile = is_dir($itemPath) ? "$itemPath/{$cfg['dataFileName']}.md" : $itemPath;
  $yaml = [];
  if( file_exists($dataFile) ) [ $yaml, ] = parse_front_matter(file_get_contents($dataFile));
  $time = isset($yaml['time']) ? substr($yaml['time'], 5, 5) : '';
  $name = $yaml['name'] ?? basename($itemPath);
?>
<div class='card mb-2 p-2' onclick="location.href='index.php?path=<?= urlencode(trim($path . '/' . $it['name'], '/')) ?>'" style='cursor:pointer'>
  <div class='d-flex justify-content-between small'>
    <div><?= htmlspecialchars($time) ?></div>
    <div><?= htmlspecialchars($name) ?></div>
  </div>
</div>
