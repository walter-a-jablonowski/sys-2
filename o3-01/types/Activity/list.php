<?php /* Activity list cell renderer */ ?>
<?php
  $cfg = app_config();
  $dataFile = "$itemPath/{$cfg['dataFileName']}.md";
  $fm = ['priority'=>0,'state'=>'new','name'=>$it['name']];
  if( file_exists($dataFile) ) {
    [$yaml,] = parse_front_matter(file_get_contents($dataFile));
    $fm = array_merge($fm, $yaml);
  }
?>
<div class='card mb-2 p-2' onclick="location.href='index.php?path=<?= urlencode(trim($path . '/' . $it['name'], '/')) ?>'" style='cursor:pointer'>
  <div class='d-flex justify-content-between'>
    <div>
      <span class='badge bg-secondary me-2'><?= $fm['priority'] ?></span><?= htmlspecialchars($fm['name']) ?>
    </div>
    <div><span class='badge bg-info'><?= htmlspecialchars($fm['state']) ?></span></div>
      <div class='btn-group btn-group-sm'>
      <button class='btn btn-light' onclick="editEntry('<?= htmlspecialchars($path . '/' . $it['name']) ?>', event)"><i class='bi bi-pencil'></i></button>
      <button class='btn btn-light' onclick="deleteEntry('<?= htmlspecialchars($path . '/' . $it['name']) ?>', event)"><i class='bi bi-trash'></i></button>
    </div>
  </div>
</div>
