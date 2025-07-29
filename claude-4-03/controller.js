// Main controller for Entry Manager application

class EntryManager
{
  constructor()
  {
    this.currentPath = 'data';
    this.currentEntry = null;
    this.navigationStack = [];
    
    this.init();
  }

  init()
  {
    this.bindEvents();
    this.loadEntries();
    this.loadTypes();
  }

  bindEvents()
  {
    // Add entry button
    document.getElementById('addEntry').addEventListener('click', () => {
      this.showAddModal();
    });

    // Save new entry
    document.getElementById('saveNewEntry').addEventListener('click', () => {
      this.saveNewEntry();
    });

    // Save edited entry
    document.getElementById('saveEditEntry').addEventListener('click', () => {
      this.saveEditedEntry();
    });

    // Sort change
    document.getElementById('sortBy').addEventListener('change', () => {
      this.loadEntries();
    });

    // Edit current entry
    document.getElementById('editEntry').addEventListener('click', () => {
      this.editCurrentEntry();
    });

    // Delete current entry
    document.getElementById('deleteEntry').addEventListener('click', () => {
      this.deleteCurrentEntry();
    });

    // Tab changes
    document.getElementById('resources-tab').addEventListener('click', () => {
      this.loadResources();
    });

    // Browser back button
    window.addEventListener('popstate', (event) => {
      if( event.state )
      {
        this.currentPath = event.state.path;
        this.currentEntry = event.state.entry;
        this.updateUI();
        this.loadEntries();
      }
    });
  }

  async apiCall( action, data = {} )
  {
    try {
      const response = await fetch('ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action, ...data })
      });

      const result = await response.json();
      
      if( ! result.success )
        throw new Error(result.error || 'Unknown error occurred');
        
      return result.data;
    }
    catch( error ) {
      this.showError(error.message);
      throw error;
    }
  }

  async loadEntries()
  {
    try {
      const entries = await this.apiCall('getEntries', { path: this.currentPath });
      this.renderEntries(entries);
    }
    catch( error ) {
      console.error('Failed to load entries:', error);
    }
  }

  async loadResources()
  {
    try {
      const resources = await this.apiCall('getResources', { path: this.currentPath });
      this.renderResources(resources);
    }
    catch( error ) {
      console.error('Failed to load resources:', error);
    }
  }

  async loadTypes()
  {
    try {
      const types = await this.apiCall('getAllowedTypes', { path: this.currentPath });
      this.populateTypeSelect(types);
    }
    catch( error ) {
      console.error('Failed to load types:', error);
    }
  }

  renderEntries( entries )
  {
    const container = document.getElementById('entryList');
    container.innerHTML = '';

    if( entries.length === 0 )
    {
      container.innerHTML = '<div class="text-center text-muted py-4">No entries found</div>';
      return;
    }

    entries.forEach(entry => {
      const card = this.createEntryCard(entry);
      container.appendChild(card);
    });
  }

  createEntryCard( entry )
  {
    const card = document.createElement('div');
    card.className = 'card entry-card';

    const cardBody = document.createElement('div');
    cardBody.className = 'card-body d-flex justify-content-between align-items-center';

    const content = document.createElement('div');
    content.className = 'flex-grow-1 entry-card-body';
    content.addEventListener('click', () => {
      this.navigateToEntry(entry);
    });

    // Render based on type
    if( entry.type === 'Activity' )
    {
      content.innerHTML = this.renderActivityCard(entry);
    }
    else if( entry.type === 'Info' )
    {
      content.innerHTML = this.renderInfoCard(entry);
    }
    else if( entry.type === 'Apartment' )
    {
      content.innerHTML = this.renderApartmentCard(entry);
    }
    else
    {
      content.innerHTML = this.renderDefaultCard(entry);
    }

    const actions = document.createElement('div');
    actions.className = 'entry-actions';
    actions.innerHTML = `
      <div class="btn-group-vertical btn-group-sm">
        <button class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); entryManager.editEntry('${entry.path}')">
          <i class="bi bi-pencil"></i>
        </button>
        <div class="dropdown">
          <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" onclick="event.stopPropagation()">
            <i class="bi bi-three-dots"></i>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); entryManager.deleteEntry('${entry.path}')">
              <i class="bi bi-trash"></i> Delete
            </a></li>
          </ul>
        </div>
      </div>
    `;

    cardBody.appendChild(content);
    cardBody.appendChild(actions);
    card.appendChild(cardBody);

    return card;
  }

  renderActivityCard( entry )
  {
    const priority = entry.priority || 3;
    const state = entry.state || 'new';
    const name = entry.name || 'Untitled';

    return `
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="badge bg-primary priority-badge me-2">${priority}</span>
          <span class="fw-medium">${name}</span>
        </div>
        <span class="badge state-badge state-${state}">${state}</span>
      </div>
    `;
  }

  renderInfoCard( entry )
  {
    const time = entry.time || '';
    const name = entry.name || 'Untitled';
    const date = time ? new Date(time).toLocaleDateString('en-US', { month: '2-digit', day: '2-digit' }) : '';

    return `
      <div class="d-flex justify-content-between align-items-center">
        <div class="entry-meta">${date}</div>
        <div class="fw-medium">${name}</div>
      </div>
    `;
  }

  renderApartmentCard( entry )
  {
    const name = entry.name || 'Untitled';
    const state = entry.state || 'new';
    const time = entry.time || '';
    const filesNr = entry.files_nr || '';
    const url = entry.url || '';
    const date = time ? new Date(time).toLocaleDateString('en-US') : '';

    const nameDisplay = url ? `<a href="${url}" target="_blank" onclick="event.stopPropagation()">${name}</a>` : name;

    return `
      <div>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div class="fw-medium">${nameDisplay}</div>
          <span class="badge state-badge state-${state}">${state}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center entry-meta">
          <small>${date}</small>
          <small>${filesNr}</small>
        </div>
      </div>
    `;
  }

  renderDefaultCard( entry )
  {
    const name = entry.name || 'Untitled';
    const time = entry.time || '';
    const date = time ? new Date(time).toLocaleDateString('en-US') : '';

    return `
      <div class="d-flex justify-content-between align-items-center">
        <div class="fw-medium">${name}</div>
        <div class="entry-meta">${date}</div>
      </div>
    `;
  }

  renderResources( resources )
  {
    const container = document.getElementById('resourcesList');
    container.innerHTML = '';

    if( resources.length === 0 )
    {
      container.innerHTML = '<div class="text-center text-muted py-4">No resources found</div>';
      return;
    }

    resources.forEach(resource => {
      const item = document.createElement('div');
      item.className = 'resource-item';

      const icon = resource.isDir ? 'bi-folder' : 'bi-file-earmark';
      const size = resource.isDir ? '' : this.formatFileSize(resource.size);

      item.innerHTML = `
        <div>
          <i class="bi ${icon}"></i>
          <span>${resource.name}</span>
        </div>
        <span class="resource-size">${size}</span>
      `;

      container.appendChild(item);
    });
  }

  formatFileSize( bytes )
  {
    if( bytes === 0 ) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
  }

  populateTypeSelect( types )
  {
    const select = document.getElementById('entryType');
    select.innerHTML = '';

    Object.keys(types).forEach(typeId => {
      const option = document.createElement('option');
      option.value = typeId;
      option.textContent = types[typeId].name || typeId;
      select.appendChild(option);
    });
  }

  navigateToEntry( entry )
  {
    if( ! entry.isDir ) return;

    // Push current state to navigation stack
    this.navigationStack.push({
      path: this.currentPath,
      entry: this.currentEntry
    });

    // Update current state
    this.currentPath = entry.path;
    this.currentEntry = entry;

    // Update browser history
    history.pushState(
      { path: this.currentPath, entry: this.currentEntry },
      '',
      `?path=${encodeURIComponent(this.currentPath)}`
    );

    this.updateUI();
    this.loadEntries();
  }

  updateUI()
  {
    // Update header
    const levelName = this.currentEntry ? this.currentEntry.name : 'Start';
    document.getElementById('currentLevel').textContent = levelName;

    // Show/hide edit and delete buttons
    const editBtn = document.getElementById('editEntry');
    const deleteBtn = document.getElementById('deleteEntry');
    
    if( this.currentEntry )
    {
      editBtn.style.display = 'block';
      deleteBtn.style.display = 'block';
      
      // Show current entry display
      this.displayCurrentEntry();
    }
    else
    {
      editBtn.style.display = 'none';
      deleteBtn.style.display = 'none';
      
      // Hide current entry display
      document.getElementById('currentEntryDisplay').style.display = 'none';
    }
  }

  async displayCurrentEntry()
  {
    if( ! this.currentEntry ) return;

    const display = document.getElementById('currentEntryDisplay');
    const content = document.getElementById('currentEntryContent');

    try {
      // Use type-specific renderer if available
      const result = await this.apiCall('getEntryRenderer', {
        path: this.currentEntry.path,
        renderer: 'read_only'
      });
      
      content.innerHTML = result.html;
    }
    catch( error ) {
      // Fallback to simple display
      content.innerHTML = `
        <h5>${this.currentEntry.name}</h5>
        <p class="text-muted mb-2">${this.currentEntry.time || ''}</p>
        <p>${this.currentEntry.description || ''}</p>
      `;
    }

    display.style.display = 'block';
  }

  showAddModal()
  {
    const modal = new bootstrap.Modal(document.getElementById('addEntryModal'));
    modal.show();
  }

  async saveNewEntry()
  {
    const form = document.getElementById('addEntryForm');
    const formData = new FormData(form);

    const data = {
      name: document.getElementById('entryName').value,
      type: document.getElementById('entryType').value,
      description: document.getElementById('entryDescription').value,
      path: this.currentPath
    };

    try {
      await this.apiCall('createEntry', data);
      
      // Close modal and refresh
      bootstrap.Modal.getInstance(document.getElementById('addEntryModal')).hide();
      form.reset();
      this.loadEntries();
      
      this.showSuccess('Entry created successfully');
    }
    catch( error ) {
      console.error('Failed to create entry:', error);
    }
  }

  async editEntry( path )
  {
    try {
      const entry = await this.apiCall('getEntry', { path });
      this.showEditModal(entry);
    }
    catch( error ) {
      console.error('Failed to load entry for editing:', error);
    }
  }

  async showEditModal( entry )
  {
    const modal = new bootstrap.Modal(document.getElementById('editEntryModal'));
    const content = document.getElementById('editEntryContent');

    try {
      // Use type-specific edit renderer if available
      const result = await this.apiCall('getEntryRenderer', {
        path: entry.path,
        renderer: 'edit'
      });
      
      content.innerHTML = result.html;
    }
    catch( error ) {
      // Fallback to simple form
      content.innerHTML = `
        <form id="editEntryForm">
          <input type="hidden" id="editEntryPath" value="${entry.path}">
          <div class="mb-3">
            <label for="editEntryName" class="form-label">Name</label>
            <input type="text" class="form-control" id="editEntryName" value="${entry.name || ''}" required>
          </div>
          <div class="mb-3">
            <label for="editEntryDescription" class="form-label">Description</label>
            <textarea class="form-control" id="editEntryDescription" rows="3">${entry.description || ''}</textarea>
          </div>
        </form>
      `;
    }

    modal.show();
  }

  async saveEditedEntry()
  {
    const path = document.getElementById('editEntryPath').value;
    const entryType = document.getElementById('editEntryType')?.value;
    
    let data = {};
    
    // Collect data based on entry type
    if( entryType === 'Activity' )
    {
      data = {
        name: document.getElementById('editActivityName').value,
        description: document.getElementById('editActivityDescription').value,
        priority: parseInt(document.getElementById('editActivityPriority').value),
        state: document.getElementById('editActivityState').value,
        dueDate: document.getElementById('editActivityDueDate').value || null
      };
    }
    else if( entryType === 'Apartment' )
    {
      data = {
        name: document.getElementById('editApartmentName').value,
        description: document.getElementById('editApartmentDescription').value,
        priority: parseInt(document.getElementById('editApartmentPriority').value),
        state: document.getElementById('editApartmentState').value,
        dueDate: document.getElementById('editApartmentDueDate').value || null,
        result: document.getElementById('editApartmentResult').value,
        url: document.getElementById('editApartmentUrl').value,
        files_nr: document.getElementById('editApartmentFilesNr').value
      };
    }
    else if( entryType === 'Info' )
    {
      data = {
        name: document.getElementById('editInfoName').value,
        description: document.getElementById('editInfoDescription').value
      };
    }
    else
    {
      // Fallback to generic form
      data = {
        name: document.getElementById('editEntryName').value,
        description: document.getElementById('editEntryDescription').value
      };
    }

    try {
      await this.apiCall('updateEntry', { path, data, entryType });
      
      // Close modal and refresh
      bootstrap.Modal.getInstance(document.getElementById('editEntryModal')).hide();
      this.loadEntries();
      
      this.showSuccess('Entry updated successfully');
    }
    catch( error ) {
      console.error('Failed to update entry:', error);
    }
  }

  async deleteEntry( path )
  {
    if( ! confirm('Are you sure you want to delete this entry?') )
      return;

    try {
      await this.apiCall('deleteEntry', { path });
      this.loadEntries();
      this.showSuccess('Entry deleted successfully');
    }
    catch( error ) {
      console.error('Failed to delete entry:', error);
    }
  }

  async editCurrentEntry()
  {
    if( this.currentEntry )
      this.editEntry(this.currentEntry.path);
  }

  async deleteCurrentEntry()
  {
    if( ! this.currentEntry ) return;

    if( ! confirm('Are you sure you want to delete this entry?') )
      return;

    try {
      await this.apiCall('deleteEntry', { path: this.currentEntry.path });
      
      // Navigate back
      if( this.navigationStack.length > 0 )
      {
        const previous = this.navigationStack.pop();
        this.currentPath = previous.path;
        this.currentEntry = previous.entry;
      }
      else
      {
        this.currentPath = 'data';
        this.currentEntry = null;
      }
      
      this.updateUI();
      this.loadEntries();
      this.showSuccess('Entry deleted successfully');
    }
    catch( error ) {
      console.error('Failed to delete entry:', error);
    }
  }

  showError( message )
  {
    const toast = document.getElementById('errorToast');
    const messageEl = document.getElementById('errorMessage');
    
    messageEl.textContent = message;
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Also log to console for debugging
    console.error('Error:', message);
  }

  showSuccess( message )
  {
    // Create success toast if it doesn't exist
    let successToast = document.getElementById('successToast');
    if( ! successToast )
    {
      const toastContainer = document.querySelector('.toast-container');
      successToast = document.createElement('div');
      successToast.id = 'successToast';
      successToast.className = 'toast';
      successToast.setAttribute('role', 'alert');
      successToast.innerHTML = `
        <div class="toast-header bg-success text-white">
          <strong class="me-auto">Success</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="successMessage"></div>
      `;
      toastContainer.appendChild(successToast);
    }
    
    const messageEl = document.getElementById('successMessage');
    messageEl.textContent = message;
    
    const bsToast = new bootstrap.Toast(successToast);
    bsToast.show();
    
    console.log('Success:', message);
  }
}

// Initialize the application
const entryManager = new EntryManager();
