<?php

namespace App;

use Symfony\Component\Yaml\Yaml;

class System
{
  private $config;
  private $types = [];

  public function __construct()
  {
    $this->config = Yaml::parseFile('config.yml');
    $this->loadTypes();
  }

  private function loadTypes()
  {
    $this->types['global'] = Yaml::parseFile('types/def.yml');

    $typeDirs = scandir('types');
    foreach ($typeDirs as $typeDir)
    {
      if ($typeDir === '.' || $typeDir === '..') continue;

      $defPath = "types/$typeDir/def.yml";
      if (is_dir("types/$typeDir") && file_exists($defPath))
      {
        $typeDef = Yaml::parseFile($defPath);
        $this->types[$typeDef['id']] = $typeDef;
      }
    }
  }

  public function getTypes()
  {
    return $this->types;
  }

  public function getType( $id )
  {
    return $this->types[$id] ?? null;
  }

  public function getEntryByPath($path)
  {
    if (empty($path)) return null;
    $fullPath = 'data/' . $path;
    if (!file_exists($fullPath)) return null;
    return $this->parseEntry($fullPath);
  }

  public function generateId($name)
  {
    // Convert each word to first character uppercase
    $name = ucwords(strtolower($name));
    // Remove all non-alpha-numeric chars
    $name = preg_replace('/[^a-zA-Z0-9]/', '', $name);
    // Add user and date
    $user = 'Default'; // Hardcoded for now
    $date = date('ymdHis');
    return "{$name}-{$user}-{$date}";
  }

  public function getConfig()
  {
    return $this->config;
  }

  public function getData( $path = '' )
  {
    $fullPath = 'data' . ($path ? '/' . $path : '');
    $items = [];

    if ( ! is_dir($fullPath) ) return [];

    $dirContents = scandir($fullPath);

    foreach ( $dirContents as $item )
    {
      if ($item === '.' || $item === '..') continue;

      $itemPath = "$fullPath/$item";
      $entry = $this->parseEntry($itemPath);
      if ( $entry ) $items[] = $entry;
    }

    // Sort items by time, newest first
    usort($items, function ($a, $b) {
      return ($a['data']['time'] ?? 0) <=> ($b['data']['time'] ?? 0);
    });

    return array_reverse($items);
  }

  public function parseEntry( $path )
  {
    $baseName = basename($path);
    $typeId = $this->identifyType($path);

    $entry = [
      'path' => $path,
      'name' => $baseName,
      'is_dir' => is_dir($path),
      'type' => $typeId
    ];

    if ( ! $typeId ) // It's a resource or group folder
    {
      return $entry;
    }

    $dataFile = $this->getDataFilePath($path);

    if ( ! $dataFile || ! file_exists($dataFile) )
    {
        $entry['type'] = null; // Cannot find data, treat as resource
        return $entry;
    }

    $content = file_get_contents($dataFile);
    $parsed = $this->parseFrontMatter($content);

    $entry['data'] = array_merge(
        $this->types['global']['fields'] ?? [],
        $this->types[$typeId]['fields'] ?? [],
        $parsed['frontMatter']
    );
    
    $entry['data']['description'] = $parsed['content'];
    $entry['name'] = $entry['data']['name'] ?? $baseName;

    return $entry;
  }

  private function identifyType( $path )
  {
    $baseName = basename($path);

    // Check all types for a regex match on the name
    foreach ( $this->types as $id => $type )
    {
      if ( $id === 'global' || empty($type['typeIdentification']) ) continue;

      if ( preg_match($type['typeIdentification'], $baseName) )
      {
        return $id;
      }
    }

    // Fallback for files: check front matter
    $dataFile = $this->getDataFilePath($path);
    if ( $dataFile && file_exists($dataFile) )
    {
        $content = file_get_contents($dataFile);
        $parsed = $this->parseFrontMatter($content);
        if ( ! empty($parsed['frontMatter']['type']) )
        {
            return $parsed['frontMatter']['type'];
        }
    }

    return null;
  }

  private function getDataFilePath( $path )
  {
    if ( is_dir($path) )
    {
      $dataFileName = $this->config['dataFileName'] ?? '-this';
      return "$path/$dataFileName.md";
    }
    elseif ( preg_match('/\.md$/', $path) )
    {
      return $path;
    }
    return null;
  }

  public function getRendererPath($typeId, $rendererName)
  {
    $path = "types/{$typeId}/{$rendererName}.php";
    if (file_exists($path)) {
        return $path;
    }
    return null;
  }

  public function deleteRecursively($path)
  {
    if (is_dir($path)) {
        $objects = scandir($path);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($path . "/" . $object) && !is_link($path . "/" . $object))
                    $this->deleteRecursively($path . "/" . $object);
                else
                    unlink($path . "/" . $object);
            }
        }
        return rmdir($path);
    } elseif (is_file($path)) {
        return unlink($path);
    }
    return false;
  }

  private function parseFrontMatter( $content )
  {
    $frontMatter = [];
    $mainContent = $content;

    if ( preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)/s', $content, $matches) )
    {
      $frontMatter = Yaml::parse($matches[1]);
      $mainContent = $matches[2];
    }

    return ['frontMatter' => $frontMatter, 'content' => $mainContent];
  }
}
