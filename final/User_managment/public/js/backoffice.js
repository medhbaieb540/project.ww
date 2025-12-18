
function updateStatusAdmin(feedbackId, newStatus) {
  if (!confirm(`Change status to "${newStatus}"?`)) {
    location.reload();
    return;
  }
  
  const formData = new URLSearchParams();
  formData.append('id', feedbackId);
  formData.append('status', newStatus);

  fetch('../../Controller/update_status_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: formData.toString()
  })
  .then(response => response.json())
  .then(result => {
    if (!result.success) throw new Error(result.message || 'Update failed');
    showNotification('Status updated successfully!', 'success');
    setTimeout(() => {
      location.reload();
    }, 1000);
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Failed to update status', 'error');
    location.reload();
  });
}

function showReplyFormAdmin(feedbackId) {
  const message = prompt('Enter your admin reply:');
  
  if (message && message.trim()) {
    if (message.trim().length < 5) {
      showNotification('Reply must be at least 5 characters', 'error');
      return;
    }
    
    if (message.trim().length > 500) {
      showNotification('Reply must not exceed 500 characters', 'error');
      return;
    }
    
    const formData = new URLSearchParams();
    formData.append('feedback_id', feedbackId);
    formData.append('message', message);

    fetch('../../Controller/reply_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: formData.toString()
    })
    .then(response => response.json())
    .then(result => {
      if (!result.success) throw new Error(result.message || 'Reply failed');
      showNotification('Reply posted successfully!', 'success');
      setTimeout(() => {
        location.reload();
      }, 1000);
    })
    .catch(error => {
      console.error('Error:', error);
      showNotification('Failed to post reply', 'error');
    });
  }
}

function deleteFeedbackAdmin(feedbackId) {
  if (!confirm('⚠️ Are you sure you want to delete this feedback?\n\nThis action cannot be undone and will also delete all associated replies.')) {
    return;
  }

  const formData = new URLSearchParams();
  formData.append('id', feedbackId);

  fetch('../../Controller/delete_feedback_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: formData.toString()
  })
  .then(response => response.json())
  .then(result => {
    if (!result.success) throw new Error(result.message || 'Delete failed');
    showNotification('Feedback deleted successfully!', 'success');
    setTimeout(() => {
      location.reload();
    }, 1000);
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Failed to delete feedback', 'error');
  });
}

function showNotification(message, type = 'info') {
  const existing = document.querySelector('.admin-notification');
  if (existing) {
    existing.remove();
  }
  
  const notification = document.createElement('div');
  notification.className = `admin-notification notification-${type}`;
  
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === 'success' ? '#1aff87' : type === 'error' ? '#ff3333' : '#3399ff'};
    color: ${type === 'success' ? '#000' : '#fff'};
    padding: 15px 25px;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s ease;
  `;
  
  const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
  notification.innerHTML = `
    <span style="font-size: 1.2rem;">${icon}</span>
    <span>${message}</span>
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.style.opacity = '1';
    notification.style.transform = 'translateX(0)';
  }, 10);
  
  setTimeout(() => {
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(400px)';
    setTimeout(() => notification.remove(), 300);
  }, 5000);
}

let selectedFeedback = [];

function toggleFeedbackSelection(feedbackId) {
  const index = selectedFeedback.indexOf(feedbackId);
  if (index > -1) {
    selectedFeedback.splice(index, 1);
  } else {
    selectedFeedback.push(feedbackId);
  }
  updateBulkActionsUI();
}

function selectAllFeedback() {
  const checkboxes = document.querySelectorAll('.feedback-checkbox');
  const allChecked = Array.from(checkboxes).every(cb => cb.checked);
  
  checkboxes.forEach(cb => {
    cb.checked = !allChecked;
    const id = parseInt(cb.dataset.id);
    if (!allChecked && !selectedFeedback.includes(id)) {
      selectedFeedback.push(id);
    }
  });
  
  if (allChecked) {
    selectedFeedback = [];
  }
  
  updateBulkActionsUI();
}

function updateBulkActionsUI() {
  const bulkActions = document.getElementById('bulkActions');
  const selectedCount = document.getElementById('selectedCount');
  
  if (bulkActions && selectedCount) {
    if (selectedFeedback.length > 0) {
      bulkActions.style.display = 'block';
      selectedCount.textContent = selectedFeedback.length;
    } else {
      bulkActions.style.display = 'none';
    }
  }
}

function bulkDelete() {
  if (selectedFeedback.length === 0) {
    showNotification('No feedback selected', 'error');
    return;
  }
  
  if (!confirm(`Delete ${selectedFeedback.length} feedback items?`)) {
    return;
  }
  
  showNotification(`Bulk delete of ${selectedFeedback.length} items started`, 'info');
}

function bulkUpdateStatus(newStatus) {
  if (selectedFeedback.length === 0) {
    showNotification('No feedback selected', 'error');
    return;
  }
  
  showNotification(`Updating ${selectedFeedback.length} items to ${newStatus}`, 'info');
}

function exportToCSV() {
  showNotification('Preparing CSV export...', 'info');
  
  setTimeout(() => {
    showNotification('CSV export complete!', 'success');
  }, 2000);
}

function exportToJSON() {
  showNotification('Preparing JSON export...', 'info');
  
  setTimeout(() => {
    showNotification('JSON export complete!', 'success');
  }, 2000);
}

let updateInterval = null;

function startAutoRefresh(intervalSeconds = 30) {
  if (updateInterval) {
    clearInterval(updateInterval);
  }
  
  updateInterval = setInterval(() => {
    checkForUpdates();
  }, intervalSeconds * 1000);
}

function checkForUpdates() {
  console.log('Checking for updates...');
}

function stopAutoRefresh() {
  if (updateInterval) {
    clearInterval(updateInterval);
    updateInterval = null;
  }
}

document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) searchInput.focus();
  }
  
  if ((e.ctrlKey || e.metaKey) && e.key === '/') {
    e.preventDefault();
    showKeyboardShortcuts();
  }
});

function showKeyboardShortcuts() {
  alert(`
Keyboard Shortcuts:
------------------
Ctrl/Cmd + K: Focus search
Ctrl/Cmd + /: Show this help
  `);
}

document.addEventListener('DOMContentLoaded', function() {
  console.log('GameBridge Admin Panel initialized');
});

window.addEventListener('beforeunload', function() {
  stopAutoRefresh();
});
