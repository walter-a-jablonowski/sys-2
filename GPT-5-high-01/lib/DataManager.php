<?php

class DataManager
{
  public static function dataRoot() : string
  {
    return Util::ensureDataRoot();
  }

  public static function normalizeRelPath( string $rel ) : string
  {
    $rel = str_replace('..', '', $rel);
    $rel = trim($rel, "/\\ ");
    return $rel;
  }

  public static function absPath( string $rel ) : string
  {
    $rel = self::normalizeRelPath($rel);
    return Util::joinPath(self::dataRoot(), $rel);
  }

  public static function isInstanceFolder( string $absDir ) : bool
  {
    return is_dir($absDir) && file_exists(Util::joinPath($absDir, Util::dataFileName() . '.md'));
  }

  public static function loadInstanceAt( string $relPath ) : ?array
  {
    $abs = self::absPath($relPath);
    if( is_dir($abs) )
    {
      $dataFile = Util::joinPath($abs, Util::dataFileName() . '.md');
      if( file_exists($dataFile) )
      {
        [$fm, $body] = Util::readFrontMatter($dataFile);
        $type = TypeManager::identify(basename($abs), $fm);
        if( isset($fm['type']) ) $type = (string)$fm['type'];
        return [ 'type' => $type, 'data' => self::mergeDefault($type, $fm, $body), 'abs' => $abs, 'rel' => $relPath ];
      }
    }
    elseif( is_file($abs) && Util::isMarkdown($abs) )
    {
      [$fm, $body] = Util::readFrontMatter($abs);
      $type = TypeManager::identify(basename($abs), $fm);
      if( isset($fm['type']) ) $type = (string)$fm['type'];
      return [ 'type' => $type, 'data' => self::mergeDefault($type, $fm, $body), 'abs' => $abs, 'rel' => $relPath ];
    }
    return null;
  }

  private static function mergeDefault( ?string $typeId, array $fm, string $body ) : array
  {
    $base = [ 'time' => Util::now(), 'name' => '', 'description' => '' ];
    if( $typeId )
    {
      $base = array_merge($base, TypeManager::defaultInstanceData($typeId));
    }
    $fm['description'] = $body;
    return array_merge($base, $fm);
  }

  public static function listAt( string $relPath ) : array
  {
    $relPath = self::normalizeRelPath($relPath);
    $abs = self::absPath($relPath);
    $isDir = is_dir($abs);
    if( $relPath === '' )
    {
      Util::ensureDir($abs);
      $isDir = true;
    }

    $current = self::loadInstanceAt($relPath);
    $currentTypeId = $current['type'] ?? null;
    $allowed = '*';
    if( $currentTypeId )
    {
      $t = TypeManager::getType($currentTypeId);
      $allowed = $t['allowedSubTypes'] ?? [];
    }

    $typed = [];
    $resources = [];

    if( $isDir )
    {
      foreach( scandir($abs) as $entry )
      {
        if( $entry === '.' || $entry === '..' ) continue;
        $entryAbs = Util::joinPath($abs, $entry);
        $entryRel = Util::joinPath($relPath, $entry);

        if( is_dir($entryAbs) )
        {
          // Typed folder?
          if( self::isInstanceFolder($entryAbs) )
          {
            $thisPath = Util::joinPath($entryRel, Util::dataFileName() . '.md');
            [$fm, $body] = Util::readFrontMatter(Util::joinPath($entryAbs, Util::dataFileName() . '.md'));
            $typeId = $fm['type'] ?? TypeManager::identify($entry, $fm);
            if( $typeId )
            {
              if( $allowed === '*' || in_array($typeId, (array)$allowed, true) )
              {
                $data = self::mergeDefault($typeId, $fm, $body);
                $typed[] = [
                  'name' => $entry,
                  'rel' => $entryRel,
                  'type' => $typeId,
                  'data' => $data
                ];
                continue;
              }
            }
          }
          // Group folder resource
          $resources[] = [ 'name' => $entry, 'rel' => $entryRel, 'isDir' => true, 'size' => 0, 'mtime' => filemtime($entryAbs) ];
        }
        elseif( is_file($entryAbs) )
        {
          if( strtolower($entry) === strtolower(Util::dataFileName() . '.md') ) continue; // skip -this.md in listing
          if( Util::isMarkdown($entryAbs) )
          {
            [$fm, $body] = Util::readFrontMatter($entryAbs);
            $typeId = $fm['type'] ?? TypeManager::identify($entry, $fm);
            if( $typeId )
            {
              if( $allowed === '*' || in_array($typeId, (array)$allowed, true) )
              {
                $data = self::mergeDefault($typeId, $fm, $body);
                $typed[] = [
                  'name' => $entry,
                  'rel' => $entryRel,
                  'type' => $typeId,
                  'data' => $data
                ];
                continue;
              }
            }
          }
          // Resource file
          $resources[] = [ 'name' => $entry, 'rel' => $entryRel, 'isDir' => false, 'size' => filesize($entryAbs), 'mtime' => filemtime($entryAbs) ];
        }
      }
    }

    // Sort typed by time desc
    usort($typed, function($a, $b)
    {
      return strcmp($b['data']['time'] ?? '', $a['data']['time'] ?? '');
    });

    return [
      'current' => $current,
      'typed' => $typed,
      'resources' => $resources
    ];
  }

  public static function renderListCell( string $typeId, array $data ) : string
  {
    $t = TypeManager::getType($typeId);
    $file = $t['listRenderer'] ?? '';
    if( ! $file || ! file_exists($file) ) return '';
    ob_start();
    $d = $data;
    include $file;
    return ob_get_clean();
  }

  public static function renderReadOnly( string $typeId, array $data ) : string
  {
    $t = TypeManager::getType($typeId);
    $file = $t['readOnlyRenderer'] ?? '';
    if( ! $file || ! file_exists($file) ) return '';
    ob_start();
    $d = $data;
    include $file;
    return ob_get_clean();
  }

  public static function renderEditForm( string $typeId, array $data, bool $isNew ) : string
  {
    $t = TypeManager::getType($typeId);
    $file = $t['editRenderer'] ?? '';
    if( ! $file || ! file_exists($file) ) return '';
    ob_start();
    $d = $data;
    $is_new = $isNew;
    $type_id = $typeId;
    include $file;
    return ob_get_clean();
  }

  public static function saveInstance( string $parentRel, string $typeId, array $data, ?string $existingRelPath = null ) : array
  {
    $parentRel = self::normalizeRelPath($parentRel);
    $parentAbs = self::absPath($parentRel);
    Util::ensureDir($parentAbs);

    $errors = TypeManager::validate($typeId, $data);
    if( $errors ) return [ 'ok' => false, 'errors' => $errors ];

    $name = trim((string)($data['name'] ?? ''));
    if( $name === '' ) $name = 'item';

    // Ensure id
    if( empty($data['id']) )
    {
      $data['id'] = Util::generateId($name);
    }

    // Special: Apartment ensure type and files_nr
    if( $typeId === 'Apartment' )
    {
      $data['type'] = 'Apartment';
      if( empty($existingRelPath) && empty($data['files_nr']) )
      {
        $data['files_nr'] = self::nextApartmentFilesNr();
      }
    }

    $displayName = TypeManager::buildDisplayName($typeId, $data);
    $description = (string)($data['description'] ?? '');

    // Remove description from front matter
    $fm = $data;
    unset($fm['description']);

    if( $typeId === 'Info' )
    {
      $targetName = Util::sanitizeName($displayName) . '.md';
      if( $existingRelPath )
      {
        $abs = self::absPath($existingRelPath);
        $dir = dirname($abs);
        $newAbs = Util::joinPath($dir, $targetName);
        if( $abs !== $newAbs ) @rename($abs, $newAbs);
        Util::writeFrontMatter($newAbs, $fm, $description);
        $newRel = Util::relPathWithin(self::dataRoot(), $newAbs);
        return [ 'ok' => true, 'rel' => $newRel ];
      }
      else
      {
        $abs = Util::joinPath($parentAbs, $targetName);
        Util::writeFrontMatter($abs, $fm, $description);
        $rel = Util::relPathWithin(self::dataRoot(), $abs);
        return [ 'ok' => true, 'rel' => $rel ];
      }
    }
    else
    {
      // Folder types
      $targetName = Util::sanitizeName($displayName);
      if( $existingRelPath )
      {
        $abs = self::absPath($existingRelPath);
        $dir = dirname($abs);
        $newDir = Util::joinPath($dir, $targetName);
        if( $abs !== $newDir ) @rename($abs, $newDir);
        $dataFile = Util::joinPath($newDir, Util::dataFileName() . '.md');
        Util::ensureDir($newDir);
        Util::writeFrontMatter($dataFile, $fm, $description);
        $newRel = Util::relPathWithin(self::dataRoot(), $newDir);
        return [ 'ok' => true, 'rel' => $newRel ];
      }
      else
      {
        $newDir = Util::joinPath($parentAbs, $targetName);
        Util::ensureDir($newDir);
        $dataFile = Util::joinPath($newDir, Util::dataFileName() . '.md');
        Util::writeFrontMatter($dataFile, $fm, $description);
        $rel = Util::relPathWithin(self::dataRoot(), $newDir);
        return [ 'ok' => true, 'rel' => $rel ];
      }
    }
  }

  public static function deletePath( string $relPath ) : bool
  {
    $abs = self::absPath($relPath);
    if( is_file($abs) ) return @unlink($abs);
    if( is_dir($abs) ) return self::rrmdir($abs);
    return false;
  }

  private static function rrmdir( string $dir ) : bool
  {
    $ok = true;
    foreach( scandir($dir) as $e )
    {
      if( $e === '.' || $e === '..' ) continue;
      $p = Util::joinPath($dir, $e);
      if( is_dir($p) )
      {
        $ok = $ok && self::rrmdir($p);
      }
      else
      {
        $ok = $ok && @unlink($p);
      }
    }
    $ok = $ok && @rmdir($dir);
    return $ok;
  }

  public static function nextApartmentFilesNr() : string
  {
    $path = Util::joinPath('user', 'Default', 'types', 'Apartment');
    Util::ensureDir($path);
    $jsonPath = Util::joinPath($path, 'files_nr.json');
    $data = Util::readJson($jsonPath, [ 'last' => 0 ]);
    $next = (int)($data['last'] ?? 0) + 1;
    if( $next > 9999 ) $next = 1; // wrap if needed
    $data['last'] = $next;
    Util::writeJson($jsonPath, $data);
    return str_pad((string)$next, 4, '0', STR_PAD_LEFT);
  }
}
