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
    source: 'children',
    offset: 0,
    total: 0,
    create: window.SYS.create || [],
    mode: window.innerWidth < 900 ? 'mobile' : 'list',
    ai: false,
    view: 'list',
    saveTimer: null,
    paletteTimer: null,
    views: {},
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
    toolbarSlot: document.querySelector('[data-role="toolbarSlot"]'),
    viewerSlot: document.querySelector('[data-role="viewerSlot"]'),
    footerSlot: document.querySelector('[data-role="footerSlot"]'),
    statusbarSlot: document.querySelector('[data-role="statusbarSlot"]'),
    saveState: document.querySelector('[data-role="saveState"]'),
    listState: document.querySelector('[data-role="listState"]'),
    crumbs: document.querySelector('[data-role="crumbs"]'),
    back: document.querySelector('[data-action="back"]'),
    navResizer: document.querySelector('[data-role="navResizer"]'),
    dropdown: document.querySelector('[data-role="dropdown"]'),
    dialog: document.querySelector('[data-role="dialog"]'),
    toast: document.querySelector('[data-role="toast"]'),
    palette: document.querySelector('[data-role="palette"]'),
    paletteInput: document.querySelector('[data-role="paletteInput"]'),
    paletteHits: document.querySelector('[data-role="paletteHits"]')
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
    rememberScroll();

    const data = await api('nav', { path: path, tab: tab || '' });

    state.path = data.path;
    state.tab = data.tab;
    state.source = 'children';
    state.offset = 0;
    state.filter = '';
    state.total = data.total;
    state.create = data.create || [];

    el.nav.innerHTML = data.html;
    el.crumbs.innerHTML = data.crumbs;
    el.back.hidden = data.isRoot;
    el.back.dataset.parent = data.parent;
    setListState(data.total);

    await restoreView();

    if( push !== false )
      writeHash();
  }

  /*
    Arriving at a container: the list moves inside it and the container goes
    into the editor, so what you navigated to is what you see. goTo on its
    own leaves the editor on whatever happened to be open before.

    Switching tab and restoring a url do not go through here: the first stays
    in the same container, the second carries its own selection.
  */

  async function enter( path )
  {
    await goTo(path, '');

    // The database itself is not an entry, there is nothing to open
    if( path !== '' )
      await select(path);
  }

  async function loadList( options )
  {
    const append = options && options.append;
    const offset = append ? state.offset : 0;

    const data = await api('list', {
      path: state.path,
      tab: state.tab,
      source: state.source,
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

    if( ! append )
      await restoreView();
  }

  /*
    Recent and pinned are not bound to the container, so switching only
    reloads the list: the header keeps naming where the main list would be.
  */

  function setSource( source )
  {
    if( source === state.source )
      return;

    state.source = source;
    state.offset = 0;

    el.nav.querySelectorAll('.pill').forEach(function ( node )
    {
      node.classList.toggle('is-active', node.dataset.source === source);
    });

    loadList();
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
    openRow(card.dataset.path);
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
    closeRow(path);
  }

  /*
    What the column looked like, per container and tab: which rows were
    unfolded and how far it was scrolled. Memory only, so a reload starts
    clean, and replayed whenever the same list is rendered again.
  */

  function viewState()
  {
    const key = state.path + '|' + state.tab;

    if( ! state.views[key])
      state.views[key] = { open: [], scroll: 0 };

    return state.views[key];
  }

  function openRow( path )
  {
    const open = viewState().open;

    if( open.indexOf(path) < 0 )
      open.push(path);
  }

  function closeRow( path )
  {
    const open = viewState().open;
    const at = open.indexOf(path);

    if( at >= 0 )
      open.splice(at, 1);
  }

  function rememberScroll()
  {
    const list = el.nav.querySelector('.list');

    if( list && state.source === 'children' )
      viewState().scroll = list.scrollTop;
  }

  /*
    Replays the rows in the order they were opened, so a parent is always
    back before the child living inside it. The scroll comes last: until the
    rows are there the offset would land somewhere else.
  */

  async function restoreView()
  {
    if( state.source !== 'children' || state.filter !== '' )
      return;

    const view = viewState();
    const done = [];

    for( const path of view.open.slice())
    {
      const card = el.nav.querySelector('.card[data-path="' + cssEscape(path) + '"]:not(.is-open)');

      if( ! card )
        continue;

      await unfold(card);
      done.push(path);
    }

    // Rows that were deleted or moved meanwhile drop out on their own
    view.open = done;

    const list = el.nav.querySelector('.list');

    if( list )
      list.scrollTop = view.scroll;
  }

  async function select( path, force )
  {
    // Already open: the second click of a double click must not fetch again
    if( ! force && path === state.selected && el.editor.querySelector('.editor'))
      return;

    await flushSave();

    const data = await api('detail', { path: path });

    state.selected = data.path;
    state.mtime = data.mtime;
    state.menu = data.menu || [];

    el.editor.innerHTML = data.editor;
    setFrame(data);
    setSaveState('');
    markSelected();
    writeHash();

    if( state.mode === 'mobile' )
      setView('editor', true);
  }

  function markSelected()
  {
    el.nav.querySelectorAll('.card, .cell, .self').forEach(function ( card )
    {
      card.classList.toggle('is-selected', card.dataset.path === state.selected);
    });
  }

  /**
   * What the viewer of the open object draws around the editor (Frame.php):
   * header, toolbar and viewer switch above, footer and status bar below.
   * Without data the frame is empty.
   */
  function setFrame( data )
  {
    el.headerSlot.innerHTML = data.header || '<span class="head-empty">No entry selected</span>';
    el.toolbarSlot.innerHTML = data.toolbar || '';
    el.viewerSlot.innerHTML = data.viewers || '';
    el.footerSlot.innerHTML = data.footer || '';
    el.statusbarSlot.innerHTML = data.statusbar || '';
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

      setFrame(data);
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
          select(state.selected, true);
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
   * Tabs inside the editor, declared by the viewer as "detailTabs".
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
      item.className = 'menu-item' + (entry.danger ? ' is-danger' : '') + (entry.current ? ' is-current' : '');
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

  /*
    Both crumb controls end in the same thing: a list of containers, and
    picking one navigates there.
  */

  function crumbDropdown( anchor, places )
  {
    if( ! places.length )
      return toast('Nothing to open at this level');

    const entries = places.map(function ( place )
    {
      return { id: 'crumb', path: place.path, label: place.title };
    });

    openDropdown(anchor, entries, function ( entry )
    {
      enter(entry.path);
    });
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

  async function runMenuEntry( entry, path )
  {
    if( entry.ajax )
    {
      const params = Object.assign({}, entry.params, { type: entry.type, viewer: entry.viewer, fn: entry.ajax, path: path });
      const data = await api('viewer', params, 'POST');

      if( data.card )
        replaceCard(data.path, data.card);

      if( data.message )
        toast(data.message);

      // Forced: the editor is open on exactly this object, but its data changed
      if( state.selected === path )
        select(path, true);

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

    if( entry.id === 'pin' )
    {
      const data = await api('pin', { path: path }, 'POST');

      toast(data.pinned ? 'Pinned' : 'Unpinned');

      if( state.source === 'pinned' )
        loadList();

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
      duplicateAltId: 'Duplicate alternative id',
      shadowed: 'Shadowed by a folder',
      extraThisFile: 'Ignored, folder has another content file',
      missingTitle: 'No title, the name holds only fields',
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
      setFrame({});
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

  // Viewers

  /*
    A type with more than one viewer gets a switch in the header. The choice
    is stored for the user, so the list and the editor are drawn again with
    the new viewer, its styles replacing those of the old one.
  */

  function viewerMenu( anchor )
  {
    openDropdown(anchor, JSON.parse(anchor.dataset.viewers), async function ( entry )
    {
      if( entry.current )
        return;

      await flushSave();

      const data = await api('use_viewer', { type: anchor.dataset.type, viewer: entry.id }, 'POST');

      useViewerAssets(data);
      await loadList();

      if( state.selected )
        await select(state.selected, true);
    });
  }

  /**
   * A script cannot be unloaded, it is only added when it is not there yet.
   */
  function useViewerAssets( data )
  {
    const old = document.querySelector('link[data-viewer-type="' + cssEscape(data.type) + '"]');

    if( old )
      old.remove();

    if( data.styles )
    {
      const link = document.createElement('link');

      link.rel = 'stylesheet';
      link.href = data.styles;
      link.dataset.viewerType = data.type;
      document.head.appendChild(link);
    }

    if( data.script && ! document.querySelector('script[src="' + cssEscape(data.script) + '"]'))
    {
      const script = document.createElement('script');

      script.src = data.script;
      document.body.appendChild(script);
    }
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

    const path = encodePath(state.path);
    const hash = '#/' + path + '?' + query.toString();

    if( location.hash === hash )
      return;

    /*
      Moving to another container is a step worth walking back, opening an
      entry is not: one history entry per card click would leave the back
      button useless for navigating.
    */

    const was = location.hash.replace(/^#\/?/, '').split('?');
    const moved = location.hash !== ''
      && ( was[0] !== path || new URLSearchParams(was[1] || '').get('tab') !== state.tab );

    if( moved )
      history.pushState({ view: state.view }, '', hash);
    else
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

  /*
    Search overlay.

    The only surface that reaches across the whole hierarchy at once. With
    an empty field it shows what was opened last, so it is useful before a
    single character is typed.
  */

  function openPalette()
  {
    closeDropdown();

    el.palette.hidden = false;
    el.paletteInput.value = '';
    el.paletteInput.focus();

    loadHits('');
  }

  function closePalette()
  {
    clearTimeout(state.paletteTimer);
    el.palette.hidden = true;
  }

  async function loadHits( query )
  {
    const data = await api('search', { q: query });

    // A slow answer to an old query must never overwrite a newer one
    if( el.palette.hidden || el.paletteInput.value.trim() !== query )
      return;

    el.paletteHits.innerHTML = data.html;
    markHit(el.paletteHits.querySelector('.hit'));
  }

  function markHit( row )
  {
    const open = el.paletteHits.querySelector('.hit.is-active');

    if( open )
      open.classList.remove('is-active');

    if( ! row )
      return;

    row.classList.add('is-active');
    row.scrollIntoView({ block: 'nearest' });
  }

  function moveHit( step )
  {
    const rows = Array.from(el.paletteHits.querySelectorAll('.hit'));

    if( ! rows.length )
      return;

    const at = rows.indexOf(el.paletteHits.querySelector('.hit.is-active'));

    markHit(rows[(Math.max(at, 0) + step + rows.length) % rows.length]);
  }

  /*
    A container is a place, so it is opened. Anything else is an entry: the
    column moves to where it lives and the entry itself goes into the editor.
  */

  async function pickHit( row )
  {
    if( ! row )
      return;

    closePalette();

    if( row.dataset.container === '1' )
      return enter(row.dataset.path);

    await goTo(row.dataset.parent, '');
    await select(row.dataset.path);
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
    const pill = event.target.closest('.pill');
    const hit = event.target.closest('.hit');

    if( ! event.target.closest('.dropdown'))
      closeDropdown();

    if( event.target === el.palette )
    {
      closePalette();
      return;
    }

    if( hit )
    {
      pickHit(hit);
      return;
    }

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

    if( pill )
    {
      setSource(pill.dataset.source);
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
          runMenuEntry(entry, card.dataset.path);
        });
      });
      return;
    }

    if( name === 'openSearch' )
      return openPalette();

    if( name === 'crumbLevel' )
    {
      api('tree', { path: node.dataset.path, pick: 'container' }).then(function ( data )
      {
        crumbDropdown(node, data.items.filter(function ( item ) { return item.isFolder; }));
      });
      return;
    }

    if( name === 'crumbSkipped' )
    {
      crumbDropdown(node, JSON.parse(node.dataset.skipped));
      return;
    }

    // The path line of a recent or pinned entry leads to its container
    if( name === 'goPath' )
    {
      enter(node.dataset.path);
      return;
    }

    if( name === 'objMenu' )
    {
      const path = state.selected || state.path;

      api('menu', { path: path }).then(function ( data )
      {
        openDropdown(node, data.entries, function ( entry )
        {
          runMenuEntry(entry, path);
        });
      });
      return;
    }

    if( name === 'viewerMenu' )
      return viewerMenu(node);

    // Toolbar and status bar buttons are entries like those of the "..." menu
    if( name === 'viewerAction' )
      return runMenuEntry(JSON.parse(node.dataset.entry), state.selected);

    if( name === 'toolbarMenu' )
    {
      openDropdown(node, JSON.parse(node.dataset.entries), function ( entry )
      {
        runMenuEntry(entry, state.selected);
      });
      return;
    }

    if( name === 'add' )
      return addEntry(node);

    if( name === 'back' )
      return enter(node.dataset.parent || '');

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

    // Phone: a row with a chevron navigates, that is what a list on a phone
    // does everywhere. The container's own editor is one [edit] away then.
    if( isContainer && state.mode === 'mobile' )
      return enter(card.dataset.path);

    // Desktop: the chevron goes deeper, one level unfolds inline, an already
    // unfolded child navigates into it
    if( isContainer && onChevron )
      return isChild ? enter(card.dataset.path) : unfold(card);

    select(card.dataset.path);
  }

  /*
    Double click enters a container, at every level. The chevron keeps the
    other job, unfolding one level in place, so the two gestures mean two
    different things instead of the same one.

    No timer is needed: the first click has already selected the entry, so
    the editor shows the container itself while the list moves inside it.
  */

  document.addEventListener('dblclick', function ( event )
  {
    const card = event.target.closest('.card');

    if( ! card || card.dataset.container !== '1' || state.mode === 'mobile' )
      return;

    // The chevron handles itself, a double click on it would undo its own work
    if( event.target.closest('.card-chevron'))
      return;

    event.preventDefault();

    enter(card.dataset.path);
  });

  document.addEventListener('input', function ( event )
  {
    const field = event.target.closest('[data-field]');

    if( ! field || ! el.editor.contains(field))
      return;

    field.closest('.field') && field.closest('.field').classList.remove('has-error');

    // Title and name fields are the file name: renaming on every keystroke would be wrong
    if( 'rename' in field.dataset )
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
      scheduleSave('rename' in field.dataset);
  }, true);

  document.addEventListener('keydown', function ( event )
  {
    if(( event.ctrlKey || event.metaKey ) && ( event.key === 'k' || event.key === 'K' ))
    {
      event.preventDefault();
      return el.palette.hidden ? openPalette() : closePalette();
    }

    if( ! el.palette.hidden )
    {
      if( event.key === 'Escape' )
        return closePalette();

      if( event.key === 'ArrowDown' || event.key === 'ArrowUp' )
      {
        event.preventDefault();
        return moveHit(event.key === 'ArrowDown' ? 1 : -1);
      }

      if( event.key === 'Enter' )
      {
        event.preventDefault();
        return pickHit(el.paletteHits.querySelector('.hit.is-active'));
      }

      return;
    }

    if( event.key === 'Escape' )
      closeDropdown();

    const field = event.target.closest && event.target.closest('input[data-rename]');

    if( field && event.key === 'Enter' )
    {
      event.preventDefault();
      field.blur();
    }
  });

  el.paletteInput.addEventListener('input', function ()
  {
    const query = el.paletteInput.value.trim();

    clearTimeout(state.paletteTimer);

    state.paletteTimer = setTimeout(function ()
    {
      loadHits(query);
    }, 140);
  });

  el.paletteHits.addEventListener('mousemove', function ( event )
  {
    const row = event.target.closest('.hit');

    if( row && ! row.classList.contains('is-active'))
      markHit(row);
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
