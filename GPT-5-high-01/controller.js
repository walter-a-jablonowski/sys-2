'use strict';

(function(){
  const $ = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

  let currentPath = '';
  let currentObj = null; // { type, data, rel, abs }

  function api(url, opts={})
  {
    return fetch(url, Object.assign({ headers: { 'Accept': 'application/json' } }, opts))
      .then(r => r.ok ? r.json() : r.json().catch(()=>({ ok:false, error: r.status+' '+r.statusText })))
      .then(j => { if(!j.ok){ throw new Error(j.error || 'Unknown error'); } return j; });
  }

  function showError(err)
  {
    console.error(err);
    alert(typeof err === 'string' ? err : (err.message || 'Error'));
  }

  function parentPath(path)
  {
    path = path || '';
    if( path === '' ) return '';
    const i = path.lastIndexOf('/');
    if( i <= 0 ) return '';
    return path.substring(0, i);
  }

  function isFilePath(path)
  {
    return /\.md$/i.test(path);
  }

  function setButtonsState()
  {
    const atRoot = currentPath === '';
    $('#btn-back').disabled = atRoot;
    const hasCurrent = !!currentObj;
    $('#btn-edit').disabled = !hasCurrent;
    $('#btn-delete').disabled = !hasCurrent;
  }

  function renderList(items)
  {
    const c = $('#list-items');
    c.innerHTML = '';
    items.forEach(it => {
      const a = document.createElement('a');
      a.href = '#';
      a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
      a.dataset.rel = it.rel;
      a.innerHTML = `
        <div class=\"flex-grow-1\">${it.cellHtml}</div>
        <div class=\"ms-2 text-muted\">›</div>
      `;
      a.addEventListener('click', (e)=>{ e.preventDefault(); navigate(it.rel); });
      c.appendChild(a);
    });
  }

  function renderResources(resources)
  {
    const c = $('#resource-items');
    c.innerHTML = '';
    resources.forEach(r => {
      const a = document.createElement('a');
      a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
      a.href = 'data/' + r.rel;
      a.target = '_blank';
      a.innerHTML = `
        <div class=\"text-truncate\" style=\"max-width:70vw\">${r.name}</div>
        <div class=\"small text-muted\">${r.isDir ? '' : r.sizeNice}</div>
      `;
      c.appendChild(a);
    });
  }

  function buildAddMenu(types)
  {
    const menu = $('#add-type-menu');
    menu.innerHTML = '';
    if( !types || types.length === 0 )
    {
      const li = document.createElement('li');
      li.innerHTML = '<span class="dropdown-item text-muted">No types</span>';
      menu.appendChild(li);
      $('#btn-add').disabled = true;
      return;
    }
    $('#btn-add').disabled = false;
    types.forEach(t => {
      const li = document.createElement('li');
      const a = document.createElement('a');
      a.href = '#';
      a.className = 'dropdown-item';
      a.textContent = t.name || t.id;
      a.addEventListener('click', (e)=>{ e.preventDefault(); openAdd(t.id, t.name || t.id); });
      li.appendChild(a);
      menu.appendChild(li);
    });
  }

  function updateBreadcrumb()
  {
    $('#breadcrumb').textContent = '/' + (currentPath || '');
  }

  function navigate(path)
  {
    load(path).catch(showError);
  }

  function load(path='')
  {
    return api('ajax.php?a=list&path=' + encodeURIComponent(path))
      .then(res => {
        currentPath = path;
        currentObj = res.current || null;
        $('#read-only').innerHTML = res.readOnlyHtml || '';
        renderList(res.items || []);
        renderResources(res.resources || []);
        buildAddMenu(res.types || []);
        updateBreadcrumb();
        setButtonsState();
      });
  }

  function serializeForm(form)
  {
    const data = {};
    $$('input, textarea, select', form).forEach(el => {
      const name = el.name;
      if(!name) return;
      if( (el.type === 'checkbox') ) data[name] = el.checked;
      else data[name] = el.value;
    });
    return data;
  }

  function openAdd(typeId, title)
  {
    const parentRel = (currentPath === '' ? '' : (isFilePath(currentPath) ? parentPath(currentPath) : currentPath));
    api('ajax.php?a=loadEditForm', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ typeId, data: {}, isNew: true })
    }).then(res => {
      $('#editModalTitle').textContent = 'Add ' + title;
      $('#editModalBody').innerHTML = res.html;
      const modal = new bootstrap.Modal($('#editModal'));
      modal.show();

      // Apartment helpers
      if( typeId === 'Apartment' )
      {
        const btn = $('#btn-get-files-nr');
        if( btn )
        {
          btn.addEventListener('click', ()=>{
            api('ajax.php?a=getNextFileNumber').then(r => {
              const inp = document.querySelector('input[name="files_nr"]');
              if( inp ) inp.value = r.number || '';
            }).catch(showError);
          });
        }
      }

      $('#btn-save').onclick = function(){
        const form = $('#type-edit-form');
        const data = serializeForm(form);
        api('ajax.php?a=saveEntry', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ parentRel, typeId, data })
        }).then(r => {
          modal.hide();
          navigate(r.rel || parentRel);
        }).catch(showError);
      };
    }).catch(showError);
  }

  function openEdit()
  {
    if( !currentObj ) return;
    const typeId = currentObj.type;
    const existingRel = currentPath;
    api('ajax.php?a=loadEditForm', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ typeId, data: currentObj.data || {}, isNew: false })
    }).then(res => {
      $('#editModalTitle').textContent = 'Edit';
      $('#editModalBody').innerHTML = res.html;
      const modal = new bootstrap.Modal($('#editModal'));
      modal.show();

      if( typeId === 'Apartment' )
      {
        const uploadBtn = $('#apt-upload-btn');
        const input = $('#apt-image-input');
        if( uploadBtn && input )
        {
          uploadBtn.addEventListener('click', ()=>{
            if( !input.files || input.files.length === 0 ) { showError('Pick an image first'); return; }
            const fd = new FormData();
            fd.append('rel', existingRel);
            fd.append('file', input.files[0]);
            fetch('ajax.php?a=apartmentUpload', { method: 'POST', body: fd })
              .then(r => r.json())
              .then(j => { if(!j.ok) throw new Error(j.error||'Upload failed'); modal.hide(); navigate(existingRel); })
              .catch(showError);
          });
        }
      }

      $('#btn-save').onclick = function(){
        const form = $('#type-edit-form');
        const data = serializeForm(form);
        api('ajax.php?a=saveEntry', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ parentRel: parentPath(existingRel), typeId, data, existingRel })
        }).then(r => {
          modal.hide();
          navigate(existingRel);
        }).catch(showError);
      };
    }).catch(showError);
  }

  function delCurrent()
  {
    if( !currentObj ) return;
    if( !confirm('Delete this item?') ) return;
    const rel = currentPath;
    api('ajax.php?a=deleteEntry', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ rel })
    }).then(() => navigate(parentPath(rel))).catch(showError);
  }

  // Buttons
  $('#btn-back').addEventListener('click', () => navigate(parentPath(currentPath)) );
  $('#btn-edit').addEventListener('click', openEdit);
  $('#btn-delete').addEventListener('click', delCurrent);
  $('#btn-refresh').addEventListener('click', () => load(currentPath).catch(showError));

  // Init
  load('').catch(showError);
})();
