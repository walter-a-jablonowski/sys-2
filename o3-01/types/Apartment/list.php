<?php /* Apartment list renderer */ ?>
<?php
  $cfg = app_config();
  $dataFile = "$itemPath/{$cfg['dataFileName']}.md";
  $yaml = [];
  if( file_exists($dataFile) ) [ $yaml, ] = parse_front_matter(file_get_contents($dataFile));
  $name = $yaml['name'] ?? $it['name'];
  $date = isset($yaml['time']) ? substr($yaml['time'], 0, 10) : '';
  $state = $yaml['state'] ?? 'new';
  $filesNr = $yaml['files_nr'] ?? '';
  $url = $yaml['url'] ?? '';
?>
<div class='card mb-2 p-2 small' onclick="location.href='index.php?path=<?= urlencode(trim($path . '/' . $it['name'], '/')) ?>'" style='cursor:pointer'>
  <div class='d-flex justify-content-between'>
    <div>
      <?php if( $url ) : ?>
        <a href='<?= htmlspecialchars($url) ?>' target='_blank'><?= htmlspecialchars($name) ?></a>
      <?php else : ?>
        <?= htmlspecialchars($name) ?>
      <?php endif; ?>
    </div>
    <div><span class='badge bg-primary'><?= htmlspecialchars($state) ?></span></div>
  </div>
  <div class='d-flex justify-content-between text-muted mt-1'>
    <div><?= htmlspecialchars($date) ?></div>
    <div><?= htmlspecialchars($filesNr) ?></div>
  </div>
</div>
