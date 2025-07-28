document.addEventListener('DOMContentLoaded', function () {
  const listContainer = document.getElementById('list-container');
  const resourceContainer = document.getElementById('resource-container');
  const subListContainer = document.querySelector('#sub-list-content .list-group');
  const subResourceContainer = document.querySelector('#sub-resources-content .list-group');

  function getCurrentPath() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('path') || '';
  }

  function handleItemClick(e) {
    const listItem = e.target.closest('.list-group-item-action');
    if (!listItem) return;

    const path = listItem.dataset.path;
    if (!path) return;

    // Handle delete button click
    if (e.target.closest('.delete-button')) {
      e.preventDefault();
      if (confirm('Are you sure you want to delete this item?')) {
        fetch('ajax.php?action=delete_entry', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ path: path }),
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            window.location.reload();
          } else {
            alert('Error: ' + result.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An unexpected error occurred.');
        });
      }
      return;
    }

    // Handle edit button click
    if (e.target.closest('.edit-button')) {
      return; // Let bootstrap handle the modal toggle
    }

    // Handle navigation click (but not on buttons/links inside)
    if (e.target.closest('a, .btn-group')) {
      return;
    }

    window.location.href = `?path=${encodeURIComponent(path)}`;
  }

  if (listContainer) listContainer.addEventListener('click', handleItemClick);
  if (resourceContainer) resourceContainer.addEventListener('click', handleItemClick);
  if (subListContainer) subListContainer.addEventListener('click', handleItemClick);
  if (subResourceContainer) subResourceContainer.addEventListener('click', handleItemClick);

  function updateTypeSpecificFields(type, containerId, isEdit = false, entryData = {}) {
    const typeDef = creatableTypes.find(t => t.id === type);
    const container = document.getElementById(containerId);
    container.innerHTML = '';

    const imageUploadContainerId = isEdit ? 'image-upload-container-edit' : 'image-upload-container-add';
    const imageUploadContainer = document.getElementById(imageUploadContainerId);

    if (type === 'Apartment') {
      imageUploadContainer.style.display = 'block';
    } else {
      imageUploadContainer.style.display = 'none';
    }

    if (typeDef && typeDef.fields) {
      typeDef.fields.forEach(field => {
        const formGroup = document.createElement('div');
        formGroup.className = 'mb-3';

        const label = document.createElement('label');
        label.className = 'form-label';
        label.setAttribute('for', `${field.id}-${isEdit ? 'edit' : 'add'}`);
        label.textContent = field.name;

        const input = document.createElement('input');
        input.type = field.type;
        input.className = 'form-control';
        input.id = `${field.id}-${isEdit ? 'edit' : 'add'}`;
        input.name = field.id;
        if (isEdit && entryData[field.id]) {
          input.value = entryData[field.id];
        }

        formGroup.appendChild(label);
        formGroup.appendChild(input);
        container.appendChild(formGroup);
      });
    }
  }

  // Add Entry Modal Logic
  const addEntryForm = document.getElementById('add-entry-form');
  const addEntryModalEl = document.getElementById('addEntryModal');
  if (addEntryForm && addEntryModalEl) {
    const addEntryModal = new bootstrap.Modal(addEntryModalEl);
    const typeSelect = document.getElementById('entry-type');

    typeSelect.addEventListener('change', function () {
      updateTypeSpecificFields(this.value, 'type-specific-fields-add');
    });

    // Initial call
    updateTypeSpecificFields(typeSelect.value, 'type-specific-fields-add');

    addEntryForm.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(this);
      formData.append('path', getCurrentPath());

      fetch('ajax.php?action=create_entry', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          addEntryModal.hide();
          window.location.reload();
        } else {
          alert('Error: ' + result.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
      });
    });
  }

  // Edit Entry Modal Logic
  const editModalEl = document.getElementById('editEntryModal');
  if (editModalEl) {
    const editModal = new bootstrap.Modal(editModalEl);
    editModalEl.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const listItem = button.closest('.list-group-item-action');
      const path = listItem.dataset.path;

      const modalPathInput = document.getElementById('edit-entry-path');
      const modalNameInput = document.getElementById('editEntryName');
      const modalDescriptionInput = document.getElementById('editEntryDescription');

      fetch(`ajax.php?action=get_entry&path=${encodeURIComponent(path)}`)
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            const entry = result.data;
            modalPathInput.value = path;
            modalNameInput.value = entry.name || '';
            modalDescriptionInput.value = entry.description || '';
            updateTypeSpecificFields(entry.type, 'type-specific-fields-edit', true, entry.data);
          } else {
            alert('Error: ' + result.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An unexpected error occurred while fetching entry data.');
        });
    });

    const editEntryForm = document.getElementById('edit-entry-form');
    editEntryForm.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(this);

      fetch('ajax.php?action=update_entry', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          editModal.hide();
          window.location.reload();
        } else {
          alert('Error: ' + result.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred while saving.');
      });
    });
  }

  const backButton = document.getElementById('back-button');
  if (backButton) {
    const currentPath = getCurrentPath();
    if (!currentPath) {
      backButton.classList.add('disabled');
    } else {
      backButton.classList.remove('disabled');
      const parentPath = currentPath.substring(0, currentPath.lastIndexOf('/'));
      backButton.href = `?path=${encodeURIComponent(parentPath)}`;
    }
  }
});
