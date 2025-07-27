<?php /* Activity read only renderer */ ?>
<?php
  $cfg = app_config();
  $dataFile = "$itemPath/{$cfg['dataFileName']}.md";
  $yaml = [];
  $body = '';
  if( file_exists($dataFile) ) [ $yaml, $body ] = parse_front_matter(file_get_contents($dataFile));
?>
<div class='p-2'>
  <h5><?= htmlspecialchars($yaml['name'] ?? $it['name']) ?></h5>
  <p><?= nl2br(htmlspecialchars($yaml['description'] ?? '')) ?></p>
  <p>Priority: <?= htmlspecialchars($yaml['priority'] ?? '') ?></p>
  <p>State: <span class='badge bg-info'><?= htmlspecialchars($yaml['state'] ?? '') ?></span></p>
  <?php if( ! empty($yaml['dueDate']) ) : ?>
    <p>Due: <?= htmlspecialchars($yaml['dueDate']) ?></p>
  <?php endif; ?>
</div>
