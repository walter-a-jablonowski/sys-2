// main.js

function toast(msg, type='danger') {
  const id = 'toast_'+Date.now();
  const tpl = `<div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body small">${msg}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>`;
  const wrap = document.createElement('div');
  wrap.innerHTML = tpl;
  document.body.appendChild(wrap.firstElementChild);
  const t = new bootstrap.Toast(document.getElementById(id), {delay:4000});
  t.show();
}

function uploadApartmentImage(id, file) {
  const fd = new FormData();
  fd.append('file', file);
  fd.append('id', id);
  fetch('ajax.php?action=apt_upload_img', {method:'POST', body: fd})
    .then(r=>r.json())
    .then(j=>{
      if(j.success) { location.reload(); }
      else toast(j.error||'Upload failed');
    })
    .catch(()=>toast('Network error'));
}

// Bind file inputs
function showAddModal() {
  const m = new bootstrap.Modal(document.getElementById('addModal'));
  m.show();
}

function submitAdd(curPath) {
  const type = document.getElementById('addType').value;
  const name = document.getElementById('addName').value.trim();
  const desc = document.getElementById('addDesc').value;
  if(!name) { toast('Name required'); return; }
  fetch('ajax.php?action=add', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({path: curPath, type, name, description: desc})
  }).then(r=>r.json()).then(j=>{
    if(j.success) { location.reload(); }
    else toast(j.error||'Error');
  }).catch(()=>toast('Network error'));
}

function editEntry(relPath, ev) {
  ev.stopPropagation();
  toast('Edit not implemented yet','warning');
}

function deleteEntry(relPath, ev) {
  ev.stopPropagation();
  if(!confirm('Delete this entry?')) return;
  fetch('ajax.php?action=delete', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({path: relPath})
  }).then(r=>r.json()).then(j=>{
    if(j.success) location.reload();
    else toast(j.error||'Delete failed');
  }).catch(()=>toast('Network error'));
}

window.addEventListener('change', e=>{
  if(e.target.matches('[data-apt-id]')) {
    uploadApartmentImage(e.target.dataset.aptId, e.target.files[0]);
  }
});
