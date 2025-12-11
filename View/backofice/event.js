// event.js - client-side controls for the event modal and validation
// Open modal: pass true for create mode, false for edit mode
function openEventModal(forCreate) {
  forCreate = (typeof forCreate === 'undefined') ? true : !!forCreate;
  if (forCreate) {
    if (document.getElementById('eventId')) document.getElementById('eventId').value = '';
    if (document.getElementById('title')) document.getElementById('title').value = '';
    if (document.getElementById('description')) document.getElementById('description').value = '';
    if (document.getElementById('organizer_id')) document.getElementById('organizer_id').value = 0;
    if (document.getElementById('eventdate')) document.getElementById('eventdate').value = '';
    if (document.getElementById('actionInput')) document.getElementById('actionInput').value = 'create';
    if (document.getElementById('modalTitle')) document.getElementById('modalTitle').textContent = 'Create New Event';
  } else {
    if (document.getElementById('modalTitle')) document.getElementById('modalTitle').textContent = 'Edit Event';
    if (document.getElementById('actionInput')) document.getElementById('actionInput').value = (document.getElementById('eventId') && document.getElementById('eventId').value) ? 'update' : 'create';
  }
  clearErrors();
  var modal = document.getElementById('eventModal');
  if (modal) modal.style.display = 'flex';
}

// alias for older name
var verif = function() { openEventModal(true); };

function closeEventModal() {
  document.getElementById('eventModal').style.display = 'none';
  // if editing was in query string, remove it so next load is clean
  if (window.location.search.indexOf('edit_id') !== -1) {
    window.location = 'event.php';
  }
}

// validation helpers
function clearErrors() {
  ['error-title','error-description','error-organizer','error-eventdate'].forEach(function(id){
    var el = document.getElementById(id);
    if (el) el.textContent = '';
  });
}

// Field-level validators (show messages live)
function validateTitleField() {
  var el = document.getElementById('title');
  var msg = document.getElementById('error-title');
  if (!el || !msg) return true;
  var v = el.value.trim();
  if (v.length < 3) { msg.textContent = 'Title must be at least 3 characters.'; return false; }
  msg.textContent = '';
  return true;
}

function validateDescriptionField() {
  var el = document.getElementById('description');
  var msg = document.getElementById('error-description');
  if (!el || !msg) return true;
  var v = el.value.trim();
  if (v.length < 10) { msg.textContent = 'Description must be at least 10 characters.'; return false; }
  msg.textContent = '';
  return true;
}

function validateOrganizerField() {
  var el = document.getElementById('organizer_id');
  var msg = document.getElementById('error-organizer');
  var preview = document.getElementById('organizer-preview');
  if (!el || !msg) return true;
  var v = el.value;
  if (v === '' || isNaN(v) || Number(v) < 0) { msg.textContent = 'Organizer ID must be a non-negative number.'; return false; }
  msg.textContent = '';
  if (preview) preview.textContent = 'Organizer ID: ' + String(Number(v));
  return true;
}

function updateOrganizerPreview() {
  var el = document.getElementById('organizer_id');
  var preview = document.getElementById('organizer-preview');
  if (!el || !preview) return;
  var v = el.value;
  if (v === '' || isNaN(v) || Number(v) < 0) { preview.textContent = ''; return; }
  preview.textContent = 'Organizer ID: ' + String(Number(v));
}

function validateDateField() {
  var el = document.getElementById('eventdate');
  var msg = document.getElementById('error-eventdate');
  if (!el || !msg) return true;
  var v = el.value;
  if (v) {
    var d = new Date(v);
    if (isNaN(d.getTime())) { msg.textContent = 'Please enter a valid date.'; return false; }
  }
  msg.textContent = '';
  return true;
}

// Organizer spinner handlers
function incrementOrganizer() {
  var el = document.getElementById('organizer_id');
  if (!el) return;
  var v = Number(el.value) || 0;
  el.value = v + 1;
  validateOrganizerField();
}

function decrementOrganizer() {
  var el = document.getElementById('organizer_id');
  if (!el) return;
  var v = Number(el.value) || 0;
  v = v - 1;
  if (v < 0) v = 0;
  el.value = v;
  validateOrganizerField();
}

// Date preview helper: show friendly date below the input and enforce min=today
function updateDatePreview() {
  var el = document.getElementById('eventdate');
  var preview = document.getElementById('date-preview');
  if (!el || !preview) return;
  var v = el.value;
  if (!v) { preview.textContent = '' ; return; }
  var d = new Date(v);
  if (isNaN(d.getTime())) { preview.textContent = ''; return; }
  var opts = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
  preview.textContent = d.toLocaleDateString(undefined, opts);
}

function validateForm() {
  clearErrors();
  var valid = true;
  var firstInvalid = null;
  var title = document.getElementById('title').value.trim();
  var description = document.getElementById('description').value.trim();
  var organizer = document.getElementById('organizer_id').value;
  var eventdate = document.getElementById('eventdate').value;

  if (title.length < 3) {
    document.getElementById('error-title').textContent = 'Title must be at least 3 characters.';
    valid = false;
    if (!firstInvalid) firstInvalid = document.getElementById('title');
  }

  if (description.length < 10) {
    document.getElementById('error-description').textContent = 'Description must be at least 10 characters.';
    valid = false;
    if (!firstInvalid) firstInvalid = document.getElementById('description');
  }

  if (organizer === '' || isNaN(organizer) || Number(organizer) < 0) {
    document.getElementById('error-organizer').textContent = 'Organizer ID must be a non-negative number.';
    valid = false;
    if (!firstInvalid) firstInvalid = document.getElementById('organizer_id');
  }

  if (eventdate) {
    var d = new Date(eventdate);
    if (isNaN(d.getTime())) {
      valid = false;
      if (!firstInvalid) firstInvalid = document.getElementById('eventdate');
    }
  }

  if (!valid && firstInvalid) {
    firstInvalid.focus();
  }
  return valid;
}

// ===== AI generator helpers =====
function setPreview(id, src) {
  var img = document.getElementById(id);
  if (!img) return;
  if (src) {
    img.src = src;
    img.style.opacity = '1';
  } else {
    img.removeAttribute('src');
    img.style.opacity = '0.4';
  }
}

async function generateAiContent() {
  var btn = document.getElementById('ai-generate-btn');
  var statusEl = document.getElementById('ai-status');
  if (!btn || !statusEl) return;
  var payload = {
    title: (document.getElementById('title') && document.getElementById('title').value ? document.getElementById('title').value : '').trim(),
    gameType: (document.getElementById('game_type') && document.getElementById('game_type').value ? document.getElementById('game_type').value : '').trim(),
    tournamentFormat: (document.getElementById('tournament_format') && document.getElementById('tournament_format').value ? document.getElementById('tournament_format').value : '').trim(),
    playerCount: (document.getElementById('player_count') && document.getElementById('player_count').value ? document.getElementById('player_count').value : '').trim(),
    theme: (document.getElementById('theme') && document.getElementById('theme').value ? document.getElementById('theme').value : '').trim()
  };
  statusEl.textContent = 'Generating with AI...';
  btn.disabled = true;
  try {
    var res = await fetch('ai_generate_event.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    var data = await res.json();
    if (data.success) {
      if (data.description && document.getElementById('description')) {
        document.getElementById('description').value = data.description;
      }
      setPreview('ai-poster-preview', data.poster);
      setPreview('ai-banner-preview', data.banner);
      setPreview('ai-thumb-preview', data.thumbnail);
      if (document.getElementById('ai_poster_data')) document.getElementById('ai_poster_data').value = data.poster || '';
      if (document.getElementById('ai_banner_data')) document.getElementById('ai_banner_data').value = data.banner || '';
      if (document.getElementById('ai_thumbnail_data')) document.getElementById('ai_thumbnail_data').value = data.thumbnail || '';
      statusEl.textContent = data.message || 'AI content ready. Review and save.';
    } else {
      statusEl.textContent = data.message || 'Unable to generate right now.';
    }
  } catch (err) {
    console.error(err);
    statusEl.textContent = 'Error reaching AI generator.';
  } finally {
    btn.disabled = false;
  }
}

// attach validation on submit when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('eventForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      if (!validateForm()) {
        e.preventDefault();
        return false;
      }
      // allow submit; server-side will still validate
    });
    // Attach live validation handlers
    var title = document.getElementById('title'); if (title) { title.addEventListener('input', validateTitleField); title.addEventListener('blur', validateTitleField); }
    var desc = document.getElementById('description'); if (desc) { desc.addEventListener('input', validateDescriptionField); desc.addEventListener('blur', validateDescriptionField); }
    var org = document.getElementById('organizer_id'); if (org) { org.addEventListener('input', function(){ validateOrganizerField(); updateOrganizerPreview(); }); org.addEventListener('blur', function(){ validateOrganizerField(); updateOrganizerPreview(); }); org.addEventListener('keydown', function(e){ if (e.key === 'ArrowUp') { e.preventDefault(); incrementOrganizer(); } if (e.key === 'ArrowDown') { e.preventDefault(); decrementOrganizer(); } }); }
    var ed = document.getElementById('eventdate');
  
    // organizer increment/decrement buttons
    var incBtn = document.getElementById('organizer-increment'); if (incBtn) incBtn.addEventListener('click', function(){ incrementOrganizer(); updateOrganizerPreview(); });
    var decBtn = document.getElementById('organizer-decrement'); if (decBtn) decBtn.addEventListener('click', function(){ decrementOrganizer(); updateOrganizerPreview(); });
    // initialize preview
    updateOrganizerPreview();
  }

  // Close modal when clicking outside
  window.addEventListener('click', function(event) {
    var modal = document.getElementById('eventModal');
    if (modal && event.target === modal) {
      closeEventModal();
    }
  });

  var aiBtn = document.getElementById('ai-generate-btn');
  if (aiBtn) {
    aiBtn.addEventListener('click', function() {
      generateAiContent();
    });
  }

  // Prefill previews if hidden fields already have data
  ['poster', 'banner', 'thumbnail'].forEach(function(key) {
    var hidden = document.getElementById('ai_' + key + '_data');
    var previewId = key === 'thumbnail' ? 'ai-thumb-preview' : 'ai-' + key + '-preview';
    if (hidden && hidden.value) {
      setPreview(previewId, hidden.value);
    }
  });
});
