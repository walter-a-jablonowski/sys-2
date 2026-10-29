<?php

namespace Sys;

/**
 * The editor. Built from the type's field definitions and the viewer's tabs,
 * so most viewers need no detail renderer of their own.
 */
class DetailRenderer extends Renderer
{
  public function render() : void
  {
    $tabs = $this->viewer->detailTabs();

    ?>
    <form class="editor" data-path="<?= $this->esc($this->obj->path) ?>"
          data-mtime="<?= $this->obj->mtime ?>" autocomplete="off">
      <?php if( $tabs ): ?>
        <div class="editor-tabs" role="tablist">
          <?php foreach( $tabs as $i => $tab ): ?>
            <button type="button" class="editor-tab<?= $i === 0 ? ' is-active' : '' ?>"
                    data-pane="<?= $this->esc($tab['id']) ?>" role="tab"><?= $this->esc($tab['label'] ?? $tab['id']) ?></button>
          <?php endforeach ?>
        </div>
        <?php foreach( $tabs as $i => $tab ): ?>
          <div class="editor-pane<?= $i === 0 ? ' is-active' : '' ?>" data-pane="<?= $this->esc($tab['id']) ?>">
            <?php $tab['id'] === 'raw' ? $this->renderRaw() : $this->renderFields($this->fieldsOfTab($tab['id'], $i === 0)) ?>
          </div>
        <?php endforeach ?>
      <?php else: ?>
        <div class="editor-pane is-active"><?php $this->renderFields($this->fields()) ?></div>
      <?php endif ?>
    </form>
    <?php
  }

  // Fields

  /**
   * The type's fields plus the fields carried in the name, which go right
   * after the title unless the type places them itself. A field of fixed
   * values is picked, not typed: chips for a list, a dropdown for one value.
   */
  protected function fields() : array
  {
    $own    = $this->type->fields();
    $fields = $this->app->path->fields($this->obj->type);
    $names  = [];

    foreach( $fields->rules() as $field => $rule )
    {
      $def = ['label' => $rule['label']];

      // A field a selection may cover offers the selections only, the value covered is a field of its own, placed before
      if( $rule['keepIn'] !== '' )
      {
        $keepIn = $rule['keepIn'];

        $names[$keepIn] = ['type' => 'select', 'options' => $fields->keptValues($field), 'labels' => $rule['labels']]
          + ($own[$keepIn] ?? ['label' => ucfirst($keepIn)]);

        unset($own[$keepIn]);
        $def += ['type' => 'select', 'options' => array_keys($rule['selected']), 'labels' => $rule['labels']];
      }
      elseif( $rule['values'] !== null )
        $def += ['type' => $rule['list'] ? 'multiselect' : 'select', 'options' => $rule['values'], 'labels' => $rule['labels']];

      if( isset($own[$field]))
        $own[$field] += $def;
      else
        $names[$field] = $def;
    }

    $at = array_search('title', array_keys($own), true);
    $at = $at === false ? 0 : $at + 1;

    return array_slice($own, 0, $at, true) + $names + array_slice($own, $at, null, true);
  }

  /**
   * Fields belonging to a detail tab. Fields without a "tab" key stay in the
   * first one, so a type only marks the exceptions.
   */
  protected function fieldsOfTab( string $tabId, bool $isFirst ) : array
  {
    $out = [];

    foreach( $this->fields() as $key => $def )
    {
      $target = $def['tab'] ?? null;

      if( $target === $tabId || ($target === null && $isFirst))
        $out[$key] = $def;
    }

    return $out;
  }

  protected function renderFields( array $fields, string $prefix = '' ) : void
  {
    foreach( $fields as $key => $def )
    {
      $name = $prefix === '' ? $key : "{$prefix}.{$key}";

      if( isset($def['fields']))
      {
        ?>
        <fieldset class="field-group">
          <legend><?= $this->esc($def['label'] ?? ucfirst($key)) ?></legend>
          <?php $this->renderFields($def['fields'], $name) ?>
        </fieldset>
        <?php
        continue;
      }

      $this->renderField($name, $def);
    }
  }

  protected function renderField( string $name, array $def ) : void
  {
    $kind = $def['type'] ?? 'text';
    $dot  = strrpos($name, '.');

    $label = $def['label'] ?? ucfirst($dot === false ? $name : substr($name, $dot + 1));
    $value = $this->value($name);
    $id    = 'f-' . str_replace('.', '-', $name);

    // The title and the fields carried in the name rename the file: saved on leaving, not per keystroke.
    // So does a value kept for the name, e.g. the priority: unless covered by a selection, the name shows it
    $rule  = $this->nameRules()[$name] ?? null;
    $kept  = in_array($name, $this->app->path->fields($this->obj->type)->kept(), true);
    $attrs = ($name === 'title' || $rule || $kept ? ' data-rename' : '') . ($rule && $rule['format'] === '' ? ' disabled' : '');

    // A name needs a title besides its fields, whatever the type says. A missing value is shown right away
    $required = ($def['required'] ?? false) || $name === 'title';
    $missing  = $required && $value === '';

    ?>
    <div class="field field-<?= $this->esc($kind) ?><?= $name === 'title' ? ' field-is-title' : '' ?><?= $missing ? ' has-error' : '' ?>">
      <label class="field-label" for="<?= $this->esc($id) ?>"><?= $this->esc($label) ?></label>
      <?php if( $kind === 'markdown' || $kind === 'textarea' ): ?>
        <textarea class="input input-area<?= $kind === 'markdown' ? ' mono' : '' ?>" id="<?= $this->esc($id) ?>"
                  data-field="<?= $this->esc($name) ?>" rows="<?= (int) ($def['rows'] ?? 14) ?>"
                  placeholder="<?= $this->esc($def['placeholder'] ?? '') ?>"<?= $attrs ?>><?= $this->esc($value) ?></textarea>
      <?php elseif( $kind === 'multiselect' ): ?>
        <?php $chosen = array_map('trim', explode(',', (string) $value)) ?>
        <input type="hidden" id="<?= $this->esc($id) ?>" data-field="<?= $this->esc($name) ?>" value="<?= $this->esc($value) ?>"<?= $attrs ?>>
        <div class="chips">
          <?php foreach( $def['options'] ?? [] as $option ): ?>
            <?php $on = in_array((string) $option, $chosen, true) ?>
            <button type="button" class="chip<?= $on ? ' is-on' : '' ?>" data-action="toggleChip" data-value="<?= $this->esc($option) ?>"
                    aria-pressed="<?= $on ? 'true' : 'false' ?>"<?= $rule && $rule['format'] === '' ? ' disabled' : '' ?>><?= $this->esc($this->optionLabel($def, $option)) ?></button>
          <?php endforeach ?>
        </div>
      <?php elseif( $kind === 'select' ): ?>
        <select class="input" id="<?= $this->esc($id) ?>" data-field="<?= $this->esc($name) ?>"<?= $attrs ?>>
          <option value=""><?= $def['required'] ?? false ? '—' : '(none)' ?></option>
          <?php foreach( $def['options'] ?? [] as $option ): ?>
            <option value="<?= $this->esc($option) ?>"<?= (string) $value === (string) $option ? ' selected' : '' ?>><?= $this->esc($this->optionLabel($def, $option)) ?></option>
          <?php endforeach ?>
        </select>
      <?php else: ?>
        <input class="input" id="<?= $this->esc($id) ?>" data-field="<?= $this->esc($name) ?>"
               type="<?= $this->esc($this->inputType($kind)) ?>" value="<?= $this->esc($value) ?>"
               placeholder="<?= $this->esc($def['placeholder'] ?? ($missing ? 'Required' : '')) ?>"
               <?= $required ? 'required' : '' ?><?= $attrs ?>>
      <?php endif ?>
    </div>
    <?php
  }

  /**
   * The front matter tab: id stays read-only, changing it would break every
   * link pointing here (plan §5.6).
   */
  protected function renderRaw() : void
  {
    $obj = $this->obj;

    ?>
    <div class="raw">
      <div class="field">
        <label class="field-label">Id</label>
        <div class="raw-id">
          <code class="mono"><?= $this->esc($obj->id) ?></code>
          <button type="button" class="btn-quiet" data-action="copyId" title="Copy id"><?= $this->icon('copy') ?></button>
        </div>
      </div>
      <div class="field">
        <label class="field-label" for="f-type">Type</label>
        <select class="input" id="f-type" data-field="__type">
          <?php foreach( $this->app->types->all() as $type ): ?>
            <?php if( in_array($type->name, ['Link', 'Group'], true) && $type->name !== $obj->type ) continue ?>
            <option value="<?= $this->esc($type->name) ?>"<?= $type->name === $obj->type ? ' selected' : '' ?>><?= $this->esc($type->label()) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="raw-keys">
        <div class="field-label">Other keys</div>
        <?php foreach( $this->unknownKeys() as $key => $value ): ?>
          <div class="raw-row">
            <code class="mono"><?= $this->esc($key) ?></code>
            <input class="input" data-field="<?= $this->esc($key) ?>" value="<?= $this->esc(is_scalar($value) ? $value : json_encode($value)) ?>"
                   <?= is_scalar($value) ? '' : 'disabled' ?>>
          </div>
        <?php endforeach ?>
        <?php if( ! $this->unknownKeys()): ?>
          <div class="empty-hint">No further keys in this file.</div>
        <?php endif ?>
      </div>
    </div>
    <?php
  }

  /**
   * The rules of the fields carried in the name, with the fixed values the
   * object's type adds.
   */
  protected function nameRules() : array
  {
    return $this->app->path->fields($this->obj->type)->rules();
  }

  /**
   * "3 – High" where the value alone says little, else the value itself.
   */
  protected function optionLabel( array $def, $option ) : string
  {
    $label = $def['labels'][$option] ?? '';

    return $label === '' ? (string) $option : "{$option} – {$label}";
  }

  // Values

  protected function value( string $path )
  {
    if( $path === 'title' )
      return $this->obj->title;

    if( $path === 'body' )
      return $this->obj->body;

    $names = $this->app->path->fields($this->obj->type);

    // A field a selection may cover is edited as the selection, its other values are the kept field's
    if( isset($names->kept()[$path]))
      return $names->selection($path, (string) ($this->obj->nameData[$path] ?? ''));

    if( array_key_exists($path, $this->obj->nameData))
      return implode(', ', (array) $this->obj->nameData[$path]);

    $value = $this->obj->data;

    foreach( explode('.', $path) as $part )
    {
      if( ! is_array($value) || ! array_key_exists($part, $value))
        return '';

      $value = $value[$part];
    }

    return is_scalar($value) ? $value : '';
  }

  /**
   * Front matter keys the type does not declare, kept verbatim on save.
   */
  protected function unknownKeys() : array
  {
    return array_diff_key($this->obj->data, $this->type->fields());
  }

  private function inputType( string $kind ) : string
  {
    return in_array($kind, ['date', 'email', 'tel', 'number', 'url'], true) ? $kind : 'text';
  }
}
