<?php /* Apartment read-only renderer */ ?>
<?php
  $cfg = app_config();
  $dataFile = "$itemPath/{$cfg['dataFileName']}.md";
  $yaml = [];
  if( file_exists($dataFile) ) [ $yaml, ] = parse_front_matter(file_get_contents($dataFile));
  $images = glob($itemPath . '/img_*');
?>
<div class='p-2'>
  <h5><?= htmlspecialchars($yaml['name'] ?? basename($itemPath)) ?></h5>
  <p><span class='badge bg-primary'><?= htmlspecialchars($yaml['state'] ?? 'new') ?></span></p>
  <p><?= nl2br(htmlspecialchars($yaml['result'] ?? '')) ?></p>
  <?php if( ! empty($yaml['url']) ) : ?>
    <p><a href='<?= htmlspecialchars($yaml['url']) ?>' target='_blank'>Open link</a></p>
  <?php endif; ?>
  <div class='row'>
    <?php foreach( $images as $img ) : $fn=basename($img); ?>
      <div class='col-6 p-1'>
        <img src='<?= htmlspecialchars($path . '/' . $it['name'] . '/' . $fn) ?>' class='img-fluid rounded'>
      </div>
    <?php endforeach; ?>
  </div>
  <div class='mt-2'>
    <input type='file' accept='image/*' capture='environment' data-apt-id='<?= htmlspecialchars($yaml['id'] ?? $it['name']) ?>' class='form-control form-control-sm'>
  </div>
</div>
