/*
  App controller.

  Large HTML always comes rendered from PHP, this file moves it into place,
  keeps the state in the hash and takes care of autosave, dialogs and the
  phone layer stack.
*/

(function ()
{
  'use strict';

  const state = {
    path: window.SYS.path,
    tab: window.SYS.tab,
    selected: null,
    mtime: 0,
    filter: '',
    offset: 0,
    total: 0,
    create: window.SYS.create || [],
    mode: window.innerWidth < 900 ? 'mobile' : 'list',
    ai: false,
    view: 'list',
    saveTimer: null,
    saving: false,
    savePending: false,
    menu: []
  };

  const el = {
    html: document.documentElement,
    nav: document.querySelector('[data-role="nav"]'),
    editor: document.querySelector('[data-role="editor"]'),
    ai: document.querySelector('[data-role="ai"]'),
    aiMessages: document.querySelector('[data-role="aiMessages"]'),
    aiComposer: document.querySelector('[data-role="aiComposer"]'),
    headerSlot: document.querySelector('[data-role="headerSlot"]'),
    footerSlot: document.querySelector('[data-role="footerSlot"]'),
    saveState: document.querySelector('[data-role="saveState"]'),
    listState: document.querySelector('[data-role="listState"]'),
    containerTitle: document.querySelector('[data-role="containerTitle"]'),
    back: document.querySelector('[data-action="back"]'),
    navResizer: document.querySelector('[data-role="navResizer"]'),
    dropdown: document.querySelector('[data-role="dropdown"]'),
    dialog: document.querySelector('[data-role="dialog"]'),
    toast: document.querySelector('[data-role="toast"]')
  };

  // Server

  async function api( action, params, method )
  {
    const payload = Object.assign({ a: action, mode: state.mode }, params || {});
    let response;

    if( method === 'POST' )
    {
      response = await fetch('ajax.php?a=' + encodeURIComponent(action), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
    }
    else
    {
      const query = new URLSearchParams();

      Object.keys(payload).forEach(function ( key )
      {
        const value = payload[key];

        if( Array.isArray(value))
          value.forEach(function ( v ) { query.append(key + '[]', v); });
        else if( value !== null && value !== undefined )
          query.append(key, value);
      });

      response = await fetch('ajax.php?' + query.toString());
    }

    let data = {};

    try { data = await response.json(); }
    catch( e ) { data = { error: 'The server sent no valid answer' }; }

    if( ! response.ok )
    {
      const error = new Error(data.error || ('Error ' + response.status));
      error.status = response.status;
      error.data = data;
      throw error;
    }

    return data;
  }

  // Navigation

  async function goTo( path, tab, push )
  {
    await flushSave();

    const data = await api('nav', { path: path, tab: tab || '' });

    state.path = data.path;
    state.tab = data.tab;
    state.offset = 0;
    state.filter = '';
    state.total = data.total;
    state.create = data.create || [];

    el.nav.innerHTML = data.html;
    el.containerTitle.textContent = data.title;
    el.back.hidden = data.isRoot;
    el.back.dataset.parent = data.parent;
    setListState(data.total);

    if( push !== false )
      writeHash();
  }

  async function loadList( options )
  {
    const append = options && options.append;
    const offset = append ? state.offset : 0;

    const data = await api('list', {
      path: state.path,
      tab: state.tab,
      offset: offset,
      filter: state.filter
    });

    const list = el.nav.querySelector('.list');

    if( ! list )
      return;

    if( append )
    {
      const more = list.querySelector('.load-more');

      if( more )
        more.remove();

      list.insertAdjacentHTML('beforeend', data.html);
    }
    else
    {
      list.innerHTML = data.html;
    }

    state.total = data.total;
    setListState(data.total);
    markSelected();
  }

  function setListState( total )
  {
    el.listState.textContent = total + (total === 1 ? ' entry' : ' entries');
  }

  // Cards

  async function unfold( card )
  {
    if( card.classList.contains('is-open'))
    {
      collapse(card);
      return;
    }

    const data = await api('list', { path: card.dataset.path, tab: 'entries', offset: 0 });
    const holder = document.createElement('div');

    holder.innerHTML = data.html;

    const children = Array.from(holder.children).reverse();

    children.forEach(function ( child )
    {
      child.classList.add('is-child');
      child.dataset.parentCard = card.dataset.path;
      card.after(child);
    });

    card.classList.add('is-open');
  }

  function collapse( card )
  {
    const path = card.dataset.path;

    el.nav.querySelectorAll('[data-parent-card="' + cssEscape(path) + '"]').forEach(function ( child )
    {
      if( child.classList.contains('is-open'))
        collapse(child);

      child.remove();
    });

    card.classList.remove('is-open');
  }

  async function select( path )
  {
    await flushSave();

    const data = await api('detail', { path: path });

    state.selected = data.path;
    state.mtime = data.mtime;
    state.menu = data.menu || [];

    el.editor.innerHTML = data.editor;
    el.headerSlot.innerHTML = data.header;
    el.footerSlot.innerHTML = data.footer;
    setSaveState('');
    markSelected();
    writeHash();

    if( state.mode === 'mobile' )
      setView('editor', true);
  }

  function markSelected()
  {
    el.nav.querySelectorAll('.card, .cell').forEach(function ( card )
    {
      card.classList.toggle('is-selected', card.dataset.path === state.selected);
    });
  }

  // Editor and autosave

  function collectFields()
  {
    const fields = {};

    el.editor.querySelectorAll('[data-field]').forEach(function ( input )
    {
      if( ! input.disabled )
        fields[input.dataset.field] = input.value;
    });

    return fields;
  }

  function scheduleSave( immediate )
  {
    clearTimeout(state.saveTimer);

    if( immediate )
    {
      save();
      return;
    }

    setSaveState('unsaved');
    state.saveTimer = setTimeout(save, 800);
  }

  async function save()
  {
    clearTimeout(state.saveTimer);

    const form = el.editor.querySelector('.editor');

    if( ! form || ! state.selected )
      return;

    if( state.saving )
    {
      state.savePending = true;
      return;
    }

    state.saving = true;
    setSaveState('saving');

    try {

      const data = await api('save', {
        path: state.selected,
        mtime: state.mtime,
        fields: collectFields()
      }, 'POST');

      state.mtime = data.mtime;
      state.selected = data.path;

      form.dataset.path = data.path;
      form.dataset.mtime = data.mtime;

      el.headerSlot.innerHTML = data.header;
      el.footerSlot.innerHTML = data.footer;

      replaceCard(data.path, data.card);
      setSaveState('saved');
      writeHash();
    }
    catch( error ) {
      onSaveError(error);
    }
    finally {
      state.saving = false;

      if( state.savePending )
      {
        state.savePending = false;
        save();
      }
    }
  }

  function onSaveError( error )
  {
    if( error.status === 409 )
    {
      setSaveState('error', 'changed on disk');
      confirmDialog(
        'The file changed on disk',
        'Someone or something else wrote this file while you were editing it. ' +
        'Your text is still in the editor.',
        'Reload from disk',
        'Keep editing'
      ).then(function ( reload )
      {
        if( reload )
          select(state.selected);
      });

      return;
    }

    if( error.status === 422 )
    {
      setSaveState('error', error.message);
      const field = error.data && error.data.field;

      if( field )
      {
        const input = el.editor.querySelector('[data-field="' + cssEscape(field) + '"]');

        if( input )
          input.closest('.field').classList.add('has-error');
      }

      return;
    }

    setSaveState('error', error.message);
    toast(error.message, true);
  }

  function flushSave()
  {
    if( state.saveTimer )
    {
      clearTimeout(state.saveTimer);
      return save();
    }

    return Promise.resolve();
  }

  function setSaveState( kind, text )
  {
    const labels = {
      '': '',
      unsaved: 'unsaved',
      saving: 'saving…',
      saved: 'saved ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      error: text || 'not saved'
    };

    el.saveState.textContent = labels[kind] !== undefined ? labels[kind] : '';
    el.saveState.className = 'save-state' + (kind ? ' is-' + kind : '');
  }

  /**
   * Tabs inside the editor, declared by the type as "detailTabs".
   */
  function showPane( button )
  {
    const form = button.closest('.editor');

    if( ! form )
      return;

    form.querySelectorAll('.editor-tab').forEach(function ( node )
    {
      node.classList.toggle('is-active', node === button);
    });

    form.querySelectorAll('.editor-pane').forEach(function ( node )
    {
      node.classList.toggle('is-active', node.dataset.pane === button.dataset.pane);
    });
  }

  function replaceCard( path, html )
  {
    const card = el.nav.querySelector('[data-path="' + cssEscape(path) + '"]');

    if( ! card || ! html )
      return;

    const holder = document.createElement('div');

    holder.innerHTML = html;

    const fresh = holder.firstElementChild;

    if( ! fresh )
      return;

    if( card.classList.contains('is-child'))
    {
      fresh.classList.add('is-child');
      fresh.dataset.parentCard = card.dataset.parentCard;
    }

    if( card.classList.contains('is-open'))
      fresh.classList.add('is-open');

    card.replaceWith(fresh);
    markSelected();
  }

  // Dropdown

  function openDropdown( anchor, entries, onPick )
  {
    el.dropdown.innerHTML = '';

    entries.forEach(function ( entry )
    {
      if( entry.divider )
      {
        el.dropdown.insertAdjacentHTML('beforeend', '<div class="menu-divider"></div>');
        return;
      }

      if( entry.label && ! entry.id )
      {
        el.dropdown.insertAdjacentHTML('beforeend', '<div class="menu-label"></div>');
        el.dropdown.lastElementChild.textContent = entry.label;
        return;
      }

      const item = document.createElement('button');

      item.type = 'button';
      item.className = 'menu-item' + (entry.danger ? ' is-danger' : '');
      item.textContent = entry.label;
      item.addEventListener('click', function ()
      {
        closeDropdown();
        onPick(entry);
      });

      el.dropdown.appendChild(item);
    });

    const box = anchor.getBoundingClientRect();

    el.dropdown.hidden = false;

    const width = el.dropdown.offsetWidth;

    el.dropdown.style.top = Math.min(box.bottom + 4, window.innerHeight - el.dropdown.offsetHeight - 8) + 'px';
    el.dropdown.style.left = Math.max(8, Math.min(box.left, window.innerWidth - width - 8)) + 'px';
  }

  function closeDropdown()
  {
    el.dropdown.hidden = true;
  }

  // Dialogs

  function dialog( title, bodyHtml, buttons )
  {
    return new Promise(function ( resolve )
    {
      el.dialog.innerHTML =
        '<div class="dialog" role="dialog" aria-modal="true">' +
          '<div class="dialog-head"></div>' +
          '<div class="dialog-body"></div>' +
          '<div class="dialog-foot"></div>' +
        '</div>';

      el.dialog.querySelector('.dialog-head').textContent = title;
      el.dialog.querySelector('.dialog-body').innerHTML = bodyHtml;

      const foot = el.dialog.querySelector('.dialog-foot');

      buttons.forEach(function ( button )
      {
        const node = document.createElement('button');

        node.type = 'button';
        node.className = 'btn' + (button.kind ? ' btn-' + button.kind : '');
        node.textContent = button.label;
        node.addEventListener('click', function () { close(button.value); });
        foot.appendChild(node);
      });

      el.dialog.hidden = false;

      const first = el.dialog.querySelector('.dialog-foot .btn:last-child');

      if( first )
        first.focus();

      function close( value )
      {
        el.dialog.hidden = true;
        el.dialog.innerHTML = '';
        document.removeEventListener('keydown', onKey);
        resolve(value);
      }

      function onKey( event )
      {
        if( event.key === 'Escape' )
          close(false);
      }

      document.addEventListener('keydown', onKey);
      el.dialog.onclick = function ( event ) { if( event.target === el.dialog ) close(false); };
      el.dialog.dataset.close = '';
      el.dialog.closeWith = close;
    });
  }

  function confirmDialog( title, text, okLabel, cancelLabel )
  {
    return dialog(title, '<p>' + escapeHtml(text) + '</p>', [
      { label: cancelLabel || 'Cancel', value: false },
      { label: okLabel || 'OK', value: true, kind: 'accent' }
    ]).then(function ( value )
    {
      return value === true;
    });
  }

  // Picker: one dialog for "move to" and "link into"

  async function picker( options )
  {
    const promise = dialog(options.title, '<div class="tree" data-role="tree"></div>', [
      { label: 'Cancel', value: false }
    ]);

    let current = options.start || '';

    async function draw()
    {
      const data = await api('tree', {
        path: current,
        pick: options.pick,
        types: options.types || [],
        exclude: options.exclude || ''
      });

      const tree = el.dialog.querySelector('[data-role="tree"]');

      if( ! tree )
        return;

      tree.innerHTML = '';

      if( ! data.isRoot )
        tree.appendChild(treeRow('↑ ' + (data.title || '…'), 'tree-up', function ()
        {
          current = data.parent;
          draw();
        }));

      if( options.pick === 'container' )
        tree.appendChild(treeRow('Put it here: ' + (data.title || 'root'), 'is-current', function ()
        {
          el.dialog.closeWith(false);
          options.onPick({ path: data.path });
        }));

      data.items.forEach(function ( item )
      {
        tree.appendChild(treeRow(item.title, '', function ()
        {
          if( options.pick === 'object' && item.pickable )
          {
            el.dialog.closeWith(false);
            options.onPick(item);
            return;
          }

          if( item.isFolder )
          {
            current = item.path;
            draw();
          }
        }));
      });

      if( ! data.items.length )
        tree.insertAdjacentHTML('beforeend', '<div class="empty-hint">Nothing in here.</div>');
    }

    function treeRow( label, cls, onClick )
    {
      const row = document.createElement('button');

      row.type = 'button';
      row.className = 'tree-item ' + cls;
      row.textContent = label;
      row.addEventListener('click', onClick);

      return row;
    }

    draw();

    return promise;
  }

  function toast( message, isError )
  {
    el.toast.textContent = message;
    el.toast.className = 'toast' + (isError ? ' is-error' : '');
    el.toast.hidden = false;

    clearTimeout(toast.timer);
    toast.timer = setTimeout(function () { el.toast.hidden = true; }, 3200);
  }

  // Object actions

  async function runMenuEntry( entry, path, type )
  {
    if( entry.ajax )
    {
      const data = await api('type', { type: type || '', fn: entry.ajax, path: path }, 'POST');

      if( data.card )
        replaceCard(data.path, data.card);

      if( data.message )
        toast(data.message);

      if( state.selected === path )
        select(path);

      return;
    }

    if( entry.js )
    {
      const handler = window.SYS_TYPES && window.SYS_TYPES[entry.js];

      if( handler )
        handler(path);
      else
        toast('No handler for ' + entry.js, true);

      return;
    }

    if( entry.id === 'rename' )
    {
      if( state.selected !== path )
        await select(path);

      const input = el.editor.querySelector('[data-field="title"]');

      if( input )
      {
        input.focus();
        input.select();
      }

      return;
    }

    if( entry.id === 'delete' )
      return remove(path);

    if( entry.id === 'move' )
      return moveTo(path);

    if( entry.id === 'link' )
      return linkInto(path);

    if( entry.id === 'reveal' )
      return dialog('Path', '<p class="mono">' + escapeHtml(window.SYS.dbName + '/' + path) + '</p>',
        [{ label: 'Close', value: false }]);

    if( entry.id === 'health' )
      return health();
  }

  async function health()
  {
    const data = await api('health');
    const kinds = {
      duplicateId: 'Duplicate id',
      shadowed: 'Shadowed by a folder',
      brokenLink: 'Broken link'
    };

    let body = '<p><b>' + data.objects + '</b> objects in <b>' + escapeHtml(data.db) + '</b>.</p>';

    if( ! data.issues.length )
    {
      body += '<p>No problems found.</p>';
    }
    else
    {
      body += '<div class="tree">';

      data.issues.forEach(function ( issue )
      {
        const what = issue.path || issue.id || '';

        body += '<div class="tree-item"><span class="warn-line">' + escapeHtml(kinds[issue.kind] || issue.kind) +
          '</span> <span class="mono">' + escapeHtml(what) + '</span></div>';
      });

      body += '</div>';
    }

    return dialog('Database check', body, [{ label: 'Close', value: false }]);
  }

  async function remove( path )
  {
    const info = await api('delete', { path: path });

    let text = '<p><b>' + escapeHtml(info.title) + '</b> is deleted for good. There is no undo.</p>';

    if( info.isFolder && info.children )
      text += '<p class="warn-line">' + info.children + ' entries inside it are deleted as well.</p>';

    if( info.links && info.links.length )
      text += '<p class="warn-line">' + info.links.length + ' link' + (info.links.length === 1 ? '' : 's') + ' pointing here will break.</p>';

    const ok = await dialog('Delete ' + info.title + '?', text, [
      { label: 'Cancel', value: false },
      { label: 'Delete', value: true, kind: 'danger' }
    ]);

    if( ok !== true )
      return;

    const data = await api('delete', { path: path, confirmed: 1 }, 'POST');

    if( state.selected === data.deleted )
    {
      state.selected = null;
      el.editor.innerHTML = '<div class="editor-empty"><p>Entry deleted.</p></div>';
      el.headerSlot.innerHTML = '<span class="head-empty">No entry selected</span>';
      el.footerSlot.innerHTML = '';
      setSaveState('');
    }

    await loadList();
    toast('Deleted');
  }

  function moveTo( path )
  {
    return picker({
      title: 'Move to',
      pick: 'container',
      start: '',
      exclude: path,
      onPick: async function ( target )
      {
        await api('move', { path: path, targetPath: target.path }, 'POST');
        await loadList();
        toast('Moved');
      }
    });
  }

  async function linkInto( path )
  {
    const obj = await api('detail', { path: path });

    return picker({
      title: 'Link "' + obj.title + '" into',
      pick: 'container',
      start: '',
      exclude: path,
      onPick: async function ( target )
      {
        await api('link', { parentPath: target.path, targetId: obj.id }, 'POST');

        if( target.path === state.path )
          await loadList();

        toast('Link created');
      }
    });
  }

  // Adding

  function addEntry( anchor )
  {
    const options = state.create || [];

    if( ! options.length )
      return;

    openDropdown(anchor, options.map(function ( option )
    {
      return option.indexOf('__link:') === 0
        ? { id: option, label: 'Link a ' + option.slice(7) }
        : { id: option, label: 'New ' + option };
    }), async function ( entry )
    {
      if( entry.id.indexOf('__link:') === 0 )
      {
        pickLinkTarget(entry.id.slice(7));
        return;
      }

      const created = await api('create', { parentPath: state.path, type: entry.id }, 'POST');

      await loadList();
      await select(created.path);

      const input = el.editor.querySelector('[data-field="title"]');

      if( input )
      {
        input.focus();
        input.select();
      }
    });
  }

  function pickLinkTarget( type )
  {
    return picker({
      title: 'Link a ' + type,
      pick: 'object',
      types: [type],
      start: '',
      onPick: async function ( item )
      {
        await api('link', { parentPath: state.path, targetId: item.id }, 'POST');
        await loadList();
        toast('Link created');
      }
    });
  }

  // Hash routing

  function writeHash()
  {
    const query = new URLSearchParams();

    query.set('tab', state.tab);

    if( state.selected )
      query.set('sel', state.selected);

    const hash = '#/' + encodePath(state.path) + '?' + query.toString();

    if( location.hash !== hash )
      history.replaceState({ view: state.view }, '', hash);
  }

  function encodePath( path )
  {
    return path.split('/').map(encodeURIComponent).join('/');
  }

  async function applyHash()
  {
    const raw = location.hash.replace(/^#\/?/, '');

    if( ! raw )
      return;

    const parts = raw.split('?');
    const path = parts[0].split('/').map(decodeURIComponent).join('/');
    const query = new URLSearchParams(parts[1] || '');

    if( path !== state.path || query.get('tab') !== state.tab )
      await goTo(path, query.get('tab') || '', false);

    const sel = query.get('sel');

    if( sel && sel !== state.selected )
      await select(sel);
  }

  // Phone layers

  function setView( view, push )
  {
    state.view = view;
    el.html.dataset.view = view;

    if( push && state.mode === 'mobile' )
      history.pushState({ view: view }, '');
  }

  function backLayer()
  {
    if( state.view === 'ai' )
    {
      setView(state.selected ? 'editor' : 'list');
      return;
    }

    setView('list');
  }

  // AI dummy

  function aiSend( text )
  {
    if( ! text.trim())
      return;

    const hint = el.aiMessages.querySelector('.ai-hint');

    if( hint )
      hint.remove();

    addMessage('user', text);

    setTimeout(function ()
    {
      addMessage('bot', 'Not connected yet — this sidebar is the finished layout waiting for its backend.');
    }, 260);
  }

  function addMessage( kind, text )
  {
    const node = document.createElement('div');

    node.className = 'ai-msg ai-msg-' + kind;
    node.textContent = text;
    el.aiMessages.appendChild(node);
    el.aiMessages.scrollTop = el.aiMessages.scrollHeight;
  }

  function toggleAi()
  {
    state.ai = ! state.ai;
    el.html.dataset.ai = state.ai ? '1' : '0';
    el.ai.hidden = ! state.ai;

    if( state.mode === 'mobile' )
      setView(state.ai ? 'ai' : 'list', true);
  }

  // Helpers

  function escapeHtml( text )
  {
    const node = document.createElement('span');

    node.textContent = text;

    return node.innerHTML;
  }

  function cssEscape( value )
  {
    return String(value).replace(/["\\]/g, '\\$&');
  }

  // Events

  document.addEventListener('click', function ( event )
  {
    const action = event.target.closest('[data-action]');
    const card = event.target.closest('.card, .cell');
    const tab = event.target.closest('.tab');
    const pane = event.target.closest('.editor-tab');
    const more = event.target.closest('.load-more');

    if( ! event.target.closest('.dropdown'))
      closeDropdown();

    if( action )
    {
      handleAction(action, event);
      return;
    }

    if( pane )
    {
      showPane(pane);
      return;
    }

    if( tab )
    {
      state.tab = tab.dataset.tab;
      el.nav.querySelectorAll('.tab').forEach(function ( node )
      {
        node.classList.toggle('is-active', node === tab);
      });
      goTo(state.path, state.tab);
      return;
    }

    if( more )
    {
      state.offset = parseInt(more.dataset.offset, 10) || 0;
      loadList({ append: true });
      return;
    }

    if( card )
      onCardClick(card, event);
  });

  function handleAction( node, event )
  {
    const name = node.dataset.action;
    const card = node.closest('.card, .cell');

    event.preventDefault();

    if( name === 'menu' && card )
    {
      api('menu', { path: card.dataset.path }).then(function ( data )
      {
        openDropdown(node, data.entries, function ( entry )
        {
          runMenuEntry(entry, card.dataset.path, data.type);
        });
      });
      return;
    }

    if( name === 'objMenu' )
    {
      const path = state.selected || state.path;

      api('menu', { path: path }).then(function ( data )
      {
        openDropdown(node, data.entries, function ( entry )
        {
          runMenuEntry(entry, path, data.type);
        });
      });
      return;
    }

    if( name === 'add' )
      return addEntry(node);

    if( name === 'back' )
      return goTo(node.dataset.parent || '', '');

    if( name === 'backLayer' )
      return backLayer();

    if( name === 'editContainer' )
      return select(state.path);

    if( name === 'toggleAi' )
      return toggleAi();

    if( name === 'copyId' )
    {
      const id = el.editor.querySelector('.raw-id code');

      if( id && navigator.clipboard )
        navigator.clipboard.writeText(id.textContent).then(function () { toast('Id copied'); });

      return;
    }

    if( name === 'aiNew' )
    {
      el.aiMessages.innerHTML = '<div class="ai-hint">New session. Still not connected.</div>';
      return;
    }

    if( name === 'aiSessions' )
      return openDropdown(node, [{ label: 'Sessions' }, { id: 's1', label: 'Chat 1' }], function () {});
  }

  function onCardClick( card, event )
  {
    const isContainer = card.dataset.container === '1';
    const isChild = card.classList.contains('is-child');
    const onChevron = !! event.target.closest('.card-chevron, .cell-chevron');

    if( isContainer && isChild )
      return goTo(card.dataset.path, '');

    if( isContainer && onChevron )
      return unfold(card);

    if( isContainer && state.mode === 'mobile' )
      return goTo(card.dataset.path, '');

    if( isContainer )
    {
      unfold(card);
      select(card.dataset.path);
      return;
    }

    select(card.dataset.path);
  }

  document.addEventListener('input', function ( event )
  {
    const field = event.target.closest('[data-field]');

    if( ! field || ! el.editor.contains(field))
      return;

    field.closest('.field') && field.closest('.field').classList.remove('has-error');

    // The title is a file name: renaming on every keystroke would be wrong
    if( field.dataset.field === 'title' )
    {
      setSaveState('unsaved');
      return;
    }

    scheduleSave(false);
  });

  document.addEventListener('change', function ( event )
  {
    const field = event.target.closest('[data-field]');

    if( field && el.editor.contains(field) && field.tagName === 'SELECT' )
      scheduleSave(true);
  });

  document.addEventListener('blur', function ( event )
  {
    const field = event.target.closest && event.target.closest('[data-field]');

    if( field && el.editor.contains(field))
      scheduleSave(field.dataset.field === 'title');
  }, true);

  document.addEventListener('keydown', function ( event )
  {
    if( event.key === 'Escape' )
      closeDropdown();

    const field = event.target.closest && event.target.closest('[data-field="title"]');

    if( field && event.key === 'Enter' )
    {
      event.preventDefault();
      field.blur();
    }
  });

  el.nav.addEventListener('input', function ( event )
  {
    if( ! event.target.classList.contains('filter-input'))
      return;

    clearTimeout(state.filterTimer);

    state.filterTimer = setTimeout(function ()
    {
      state.filter = event.target.value.trim();
      state.offset = 0;
      loadList();
    }, 180);
  });

  el.aiComposer.addEventListener('submit', function ( event )
  {
    event.preventDefault();

    const input = el.aiComposer.querySelector('.ai-input');

    aiSend(input.value);
    input.value = '';
    input.style.height = 'auto';
  });

  el.aiComposer.addEventListener('input', function ( event )
  {
    const input = event.target;

    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 140) + 'px';
  });

  el.aiComposer.addEventListener('keydown', function ( event )
  {
    if( event.key === 'Enter' && ! event.shiftKey )
    {
      event.preventDefault();
      el.aiComposer.requestSubmit ? el.aiComposer.requestSubmit() : el.aiComposer.dispatchEvent(new Event('submit'));
    }
  });

  /*
    Dragging the line between the navigation column and the content area.
    Both bands read the same variable, so they follow along by themselves.
  */

  el.navResizer.addEventListener('pointerdown', function ( event )
  {
    if( state.mode === 'mobile' )
      return;

    const styles = getComputedStyle(el.html);
    const min = parseInt(styles.getPropertyValue('--nav-min'), 10) || 300;
    const max = parseInt(styles.getPropertyValue('--nav-max'), 10) || 560;

    event.preventDefault();
    el.navResizer.setPointerCapture(event.pointerId);
    el.navResizer.classList.add('is-dragging');
    document.body.style.userSelect = 'none';

    function onMove( move )
    {
      const width = Math.max(min, Math.min(max, move.clientX));

      el.html.style.setProperty('--nav-w', width + 'px');
    }

    function onUp()
    {
      el.navResizer.classList.remove('is-dragging');
      document.body.style.userSelect = '';
      el.navResizer.removeEventListener('pointermove', onMove);
      el.navResizer.removeEventListener('pointerup', onUp);
    }

    el.navResizer.addEventListener('pointermove', onMove);
    el.navResizer.addEventListener('pointerup', onUp);
  });

  // Double click returns to the width the theme defines

  el.navResizer.addEventListener('dblclick', function ()
  {
    el.html.style.removeProperty('--nav-w');
  });

  window.addEventListener('popstate', function ()
  {
    if( state.mode === 'mobile' && state.view !== 'list' )
    {
      backLayer();
      return;
    }

    applyHash();
  });

  window.addEventListener('resize', function ()
  {
    const mode = window.innerWidth < 900 ? 'mobile' : 'list';

    if( mode === state.mode )
      return;

    state.mode = mode;
    setView('list');
    loadList();
  });

  window.addEventListener('beforeunload', function ()
  {
    if( state.saveTimer )
      save();
  });

  // Start

  el.html.dataset.view = 'list';

  if( location.hash )
    applyHash();
  else if( state.mode === 'mobile' )
    loadList();

  window.SYS_APP = {
    api: api,
    select: select,
    goTo: goTo,
    toast: toast,
    reloadList: loadList
  };
})();
