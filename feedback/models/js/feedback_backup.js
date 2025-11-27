

function toggleNewFeedbackForm() {
  const form = document.getElementById('newFeedbackForm');
  form.classList.toggle('active');
  
  if (form.classList.contains('active')) {
    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

function toggleStatusField() {
  const type = document.getElementById('feedbackType').value;
  const statusGroup = document.getElementById('statusGroup');
  const gameSelectGroup = document.getElementById('gameSelectGroup');
  
  // Show status and game selection only for reports
  statusGroup.style.display = type === 'report' ? 'block' : 'none';
  gameSelectGroup.style.display = type === 'report' ? 'block' : 'none';
  
  // Hide manual game input if report is selected
  const gameNameGroup = document.getElementById('gameNameGroup');
  gameNameGroup.style.display = type === 'report' ? 'none' : 'block';
}



function validateGameName() {
  const gameInput = document.getElementById('gameName');
  const errorMsg = document.getElementById('gameNameError');
  const value = gameInput.value.trim();
  
  
  errorMsg.textContent = '';
  gameInput.classList.remove('error');
  
  if (value === '') {
    showError(gameInput, errorMsg, 'Game name is required');
    return false;
  }
  
  if (value.length < 2) {
    showError(gameInput, errorMsg, 'Game name must be at least 2 characters');
    return false;
  }
  
  if (value.length > 100) {
    showError(gameInput, errorMsg, 'Game name must not exceed 100 characters');
    return false;
  }
  

  gameInput.classList.add('success');
  return true;
}

function validateMessage() {
  const messageInput = document.getElementById('message');
  const errorMsg = document.getElementById('messageError');
  const value = messageInput.value.trim();
  

  errorMsg.textContent = '';
  messageInput.classList.remove('error');
  
  if (value === '') {
    showError(messageInput, errorMsg, 'Message is required');
    return false;
  }
  
  if (value.length < 10) {
    showError(messageInput, errorMsg, 'Message must be at least 10 characters');
    return false;
  }
  
  if (value.length > 1000) {
    showError(messageInput, errorMsg, 'Message must not exceed 1000 characters');
    return false;
  }
  

  messageInput.classList.add('success');
  return true;
}

function validateGameSelect() {
  const gameSelect = document.getElementById('gameSelect');
  const errorMsg = document.getElementById('gameSelectError');
  

  errorMsg.textContent = '';
  gameSelect.classList.remove('error');
  
  if (gameSelect.value === '') {
    showError(gameSelect, errorMsg, 'Please select a game to report');
    return false;
  }
  

  gameSelect.classList.add('success');
  return true;
}

function showError(input, errorElement, message) {
  input.classList.add('error');
  errorElement.textContent = '⚠ ' + message;
  errorElement.style.display = 'block';
}


document.addEventListener('DOMContentLoaded', function() {
  const gameName = document.getElementById('gameName');
  const message = document.getElementById('message');
  const gameSelect = document.getElementById('gameSelect');
  const feedbackType = document.getElementById('feedbackType');
  
  if (gameName) {
    gameName.addEventListener('blur', validateGameName);
    gameName.addEventListener('input', function() {
      if (this.classList.contains('error')) {
        validateGameName();
      }
    });
  }
  
  if (message) {
    message.addEventListener('blur', validateMessage);
    message.addEventListener('input', function() {
      if (this.classList.contains('error')) {
        validateMessage();
      }

      updateCharacterCount();
    });
  }
  
  if (gameSelect) {
    gameSelect.addEventListener('change', function() {
      if (this.classList.contains('error')) {
        validateGameSelect();
      }
    });
  }
  
  if (feedbackType) {
    feedbackType.addEventListener('change', toggleStatusField);
  }
});

function updateCharacterCount() {
  const message = document.getElementById('message');
  const counter = document.getElementById('charCounter');
  
  if (message && counter) {
    const current = message.value.length;
    const max = 1000;
    counter.textContent = current + ' / ' + max;
    
    if (current > max) {
      counter.style.color = '#ff3333';
    } else if (current > max * 0.9) {
      counter.style.color = '#ffcc00';
    } else {
      counter.style.color = '#aaa';
    }
  }
}



function submitFeedback(event) {
  event.preventDefault();
  
  const type = document.getElementById('feedbackType').value;
  let isValid = true;
  

  if (type === 'report') {
    // For reports, validate game selection
    if (!validateGameSelect()) isValid = false;
  } else {
    // For feedback, validate game name input
    if (!validateGameName()) isValid = false;
  }
  
  // Always validate message
  if (!validateMessage()) isValid = false;
  
  // Stop if validation fails
  if (!isValid) {
    showNotification('Please fix the errors before submitting', 'error');
    return;
  }
  
  // Prepare form data
  const formData = new FormData();
  
  if (type === 'report') {
    formData.append('game', document.getElementById('gameSelect').value);
    formData.append('status', document.getElementById('status').value);
  } else {
    formData.append('game', document.getElementById('gameName').value);
  }
  
  formData.append('type', type);
  formData.append('message', document.getElementById('message').value);
  
  // Show loading state
  const submitBtn = event.target.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;
  submitBtn.innerHTML = '⏳ Submitting...';
  submitBtn.disabled = true;
  
  fetch('module/submit.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(result => {
    showNotification('Feedback submitted successfully!', 'success');
    setTimeout(() => {
      location.reload();
    }, 1500);
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Failed to submit feedback. Please try again.', 'error');
    submitBtn.innerHTML = originalText;
    submitBtn.disabled = false;
  });
}

// ============================================
// UPDATE STATUS
// ============================================

function updateStatus(feedbackId, newStatus) {
  const formData = new FormData();
  formData.append('id', feedbackId);
  formData.append('status', newStatus);

  fetch('module/updatefeedback.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(result => {
    showNotification('Status updated successfully!', 'success');
    setTimeout(() => {
      location.reload();
    }, 1000);
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Failed to update status', 'error');
  });
}

// ============================================
// REPLY TO FEEDBACK
// ============================================

function showReplyForm(feedbackId) {
  const message = prompt('Enter your reply:');
  
  if (message && message.trim()) {
    // Validate reply length
    if (message.trim().length < 5) {
      showNotification('Reply must be at least 5 characters', 'error');
      return;
    }
    
    if (message.trim().length > 500) {
      showNotification('Reply must not exceed 500 characters', 'error');
      return;
    }
    
    const formData = new FormData();
    formData.append('feedback_id', feedbackId);
    formData.append('message', message);

    fetch('module/reply.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(result => {
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

// ============================================
// DELETE FEEDBACK
// ============================================

function deleteFeedback(feedbackId) {
  if (!confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) {
    return;
  }

  const formData = new FormData();
  formData.append('id', feedbackId);

  fetch('module/deletefeedback.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(result => {
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

// ============================================
// NOTIFICATION SYSTEM
// ============================================

function showNotification(message, type = 'info') {
  // Remove existing notifications
  const existing = document.querySelector('.notification');
  if (existing) {
    existing.remove();
  }
  
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  
  const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
  notification.innerHTML = `
    <span class="notification-icon">${icon}</span>
    <span class="notification-message">${message}</span>
  `;
  
  document.body.appendChild(notification);
  
  // Trigger animation
  setTimeout(() => notification.classList.add('show'), 10);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => notification.remove(), 300);
  }, 5000);
}

// ============================================
// FILTER AND SEARCH (FOR BACKOFFICE)
// ============================================

function filterFeedback(filterType, filterValue) {
  const cards = document.querySelectorAll('.feedback-card');
  
  cards.forEach(card => {
    let show = true;
    
    if (filterType === 'type' && filterValue !== 'all') {
      const type = card.querySelector('.feedback-type').textContent.toLowerCase();
      show = type.includes(filterValue);
    } else if (filterType === 'status' && filterValue !== 'all') {
      const status = card.querySelector('.status-select')?.value || 'none';
      show = status === filterValue;
    } else if (filterType === 'game') {
      const game = card.querySelector('h3').textContent.toLowerCase();
      show = game.includes(filterValue.toLowerCase());
    }
    
    card.style.display = show ? 'block' : 'none';
  });
}

function searchFeedback(searchTerm) {
  const cards = document.querySelectorAll('.feedback-card');
  const term = searchTerm.toLowerCase();
  
  cards.forEach(card => {
    const game = card.querySelector('h3').textContent.toLowerCase();
    const message = card.querySelector('p').textContent.toLowerCase();
    const author = card.querySelector('small').textContent.toLowerCase();
    
    const matches = game.includes(term) || message.includes(term) || author.includes(term);
    card.style.display = matches ? 'block' : 'none';
  });
}
