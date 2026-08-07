<?php

namespace Sys;

/**
 * The editor. Built from the type's field definitions, so most types need no
 * detail renderer of their own.
 */
class DetailRenderer extends Renderer
{
  public function render() : void
  {
    $tabs = $this->type->detailTabs();

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
        <div class="editor-pane is-active"><?php $this->renderFields($this->type->fields()) ?></div>
      <?php endif ?>
    </form>
    <?php
  }

  // Fields

  /**
   * Fields belonging to a detail tab. Fields without a "tab" key stay in the
   * first one, so a type only marks the exceptions.
   */
  protected function fieldsOfTab( string $tabId, bool $isFirst ) : array
  {
    $out = [];

    foreach( $this->type->fields() as $key => $def )
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

    ?>
    <div class="field field-<?= $this->esc($kind) ?><?= $name === 'title' ? ' field-is-title' : '' ?>">
      <label class="field-label" for="<?= $this->esc($id) ?>"><?= $this->esc($label) ?></label>
      <?php if( $kind === 'markdown' || $kind === 'textarea' ): ?>
        <textarea class="input input-area<?= $kind === 'markdown' ? ' mono' : '' ?>" id="<?= $this->esc($id) ?>"
                  data-field="<?= $this->esc($name) ?>" rows="<?= (int) ($def['rows'] ?? 14) ?>"
                  placeholder="<?= $this->esc($def['placeholder'] ?? '') ?>"><?= $this->esc($value) ?></textarea>
      <?php elseif( $kind === 'select' ): ?>
        <select class="input" id="<?= $this->esc($id) ?>" data-field="<?= $this->esc($name) ?>">
          <option value=""><?= $def['required'] ?? false ? '—' : '(none)' ?></option>
          <?php foreach( $def['options'] ?? [] as $option ): ?>
            <option value="<?= $this->esc($option) ?>"<?= (string) $value === (string) $option ? ' selected' : '' ?>><?= $this->esc($option) ?></option>
          <?php endforeach ?>
        </select>
      <?php else: ?>
        <input class="input" id="<?= $this->esc($id) ?>" data-field="<?= $this->esc($name) ?>"
               type="<?= $this->esc($this->inputType($kind)) ?>" value="<?= $this->esc($value) ?>"
               placeholder="<?= $this->esc($def['placeholder'] ?? '') ?>"
               <?= ($def['required'] ?? false) ? 'required' : '' ?>>
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

  // Values

  protected function value( string $path )
  {
    if( $path === 'title' )
      return $this->obj->title;

    if( $path === 'body' )
      return $this->obj->body;

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
