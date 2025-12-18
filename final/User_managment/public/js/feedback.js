const endpoints = {
  submit: '../../Controller/submit_feedback_action.php',
  reply: '../../Controller/reply_action.php',
  update: '../../Controller/update_status_action.php',
  delete: '../../Controller/delete_feedback_action.php'
};

function toast(message, type = 'info') {
  const existing = document.querySelector('.notification');
  if (existing) existing.remove();

  const el = document.createElement('div');
  el.className = `notification notification-${type}`;
  el.innerHTML = `
    <span class="notification-icon">${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
    <span class="notification-message">${message}</span>
  `;
  document.body.appendChild(el);
  requestAnimationFrame(() => el.classList.add('show'));
  setTimeout(() => {
    el.classList.remove('show');
    setTimeout(() => el.remove(), 250);
  }, 3200);
}

function postForm(url, data) {
  return fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data).toString()
  }).then(r => r.json());
}

function handleSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Submitting...';

  const data = {
    game: form.game.value.trim(),
    type: form.type.value,
    message: form.message.value.trim(),
    status: form.status ? form.status.value : 'pending'
  };

  postForm(endpoints.submit, data)
    .then(res => {
      if (res.success) {
        toast('Feedback submitted!', 'success');
        setTimeout(() => window.location.reload(), 800);
      } else {
        throw new Error(res.message || 'Unable to submit');
      }
    })
    .catch(err => {
      toast(err.message, 'error');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit';
    });
}

function bindReplyButtons() {
  document.querySelectorAll('.reply-form').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      const id = form.dataset.feedbackId;
      const message = form.querySelector('input[name="reply_message"]').value.trim();
      if (!message) {
        toast('Reply message is required', 'error');
        return;
      }
      postForm(endpoints.reply, { feedback_id: id, message })
        .then(res => {
          if (res.success) {
            toast('Reply posted', 'success');
            setTimeout(() => window.location.reload(), 600);
          } else {
            throw new Error(res.message || 'Reply failed');
          }
        })
        .catch(err => toast(err.message, 'error'));
    });
  });
}

function bindStatusSelects() {
  document.querySelectorAll('.status-select-inline').forEach(select => {
    select.addEventListener('change', () => {
      const id = select.dataset.feedbackId;
      const status = select.value;
      postForm(endpoints.update, { feedback_id: id, status })
        .then(res => {
          if (!res.success) throw new Error(res.message || 'Update failed');
          toast('Status updated', 'success');
        })
        .catch(err => {
          toast(err.message, 'error');
          select.value = select.dataset.current;
        });
    });
  });
}

function bindDeleteButtons() {
  document.querySelectorAll('.delete-feedback').forEach(btn => {
    btn.addEventListener('click', () => {
      if (!confirm('Delete this feedback?')) return;
      const id = btn.dataset.feedbackId;
      postForm(endpoints.delete, { feedback_id: id })
        .then(res => {
          if (!res.success) throw new Error(res.message || 'Delete failed');
          toast('Feedback deleted', 'success');
          setTimeout(() => window.location.reload(), 600);
        })
        .catch(err => toast(err.message, 'error'));
    });
  });
}

function bindReplyToggles() {
  document.querySelectorAll('.toggle-reply').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      if (target) target.classList.toggle('hidden');
    });
  });
}

function setupFilters() {
  const searchInput = document.getElementById('feedbackSearch');
  if (!searchInput) return;
  searchInput.addEventListener('input', () => {
    const term = searchInput.value.toLowerCase();
    document.querySelectorAll('.feedback-card').forEach(card => {
      const text = card.textContent.toLowerCase();
      card.style.display = text.includes(term) ? 'block' : 'none';
    });
  });
}

window.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('feedbackForm');
  if (form) form.addEventListener('submit', handleSubmit);
  bindReplyButtons();
  bindStatusSelects();
  bindDeleteButtons();
  bindReplyToggles();
  setupFilters();
});
