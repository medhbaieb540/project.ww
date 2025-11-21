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
      statusGroup.style.display = type === 'report' ? 'block' : 'none';
    }

    function submitFeedback(event) {
      event.preventDefault();
      
      const formData = new FormData();
      formData.append('game', document.getElementById('gameName').value);
      formData.append('type', document.getElementById('feedbackType').value);
      formData.append('message', document.getElementById('message').value);
      
      if (document.getElementById('feedbackType').value === 'report') {
        formData.append('status', document.getElementById('status').value);
      }

      fetch('submit.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(result => {
        alert('Feedback submitted successfully!');
        location.reload();
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to submit feedback');
      });
    }

    function updateStatus(feedbackId, newStatus) {
      const formData = new FormData();
      formData.append('id', feedbackId);
      formData.append('status', newStatus);

      fetch('updatefeedback.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(result => {
        event.target.className = 'status-select status-' + newStatus;
        location.reload();
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to update status');
        location.reload();
      });
    }

    function showReplyForm(feedbackId) {
      const message = prompt('Enter your reply:');
      
      if (message && message.trim()) {
        const formData = new FormData();
        formData.append('feedback_id', feedbackId);
        formData.append('message', message);

        fetch('reply.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(result => {
          alert('Reply posted!');
          location.reload();
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Failed to post reply');
        });
      }
    }

    function deleteFeedback(feedbackId) {
      if (!confirm('Delete this feedback?')) return;

      const formData = new FormData();
      formData.append('id', feedbackId);

      fetch('deletefeedback.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(result => {
        alert('Deleted!');
        location.reload();
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete feedback');
      });
    }









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
      statusGroup.style.display = type === 'report' ? 'block' : 'none';
    }

    function submitFeedback(event) {
      event.preventDefault();
      
      const formData = new FormData();
      formData.append('game', document.getElementById('gameName').value);
      formData.append('type', document.getElementById('feedbackType').value);
      formData.append('message', document.getElementById('message').value);
      
      if (document.getElementById('feedbackType').value === 'report') {
        formData.append('status', document.getElementById('status').value);
      }

      fetch('submit.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(result => {
        alert('Feedback submitted successfully!');
        location.reload();
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to submit feedback');
      });
    }

    function updateStatus(feedbackId, newStatus) {
      const formData = new FormData();
      formData.append('id', feedbackId);
      formData.append('status', newStatus);

      fetch('updatefeedback.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(result => {
        event.target.className = 'status-select status-' + newStatus;
        location.reload();
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to update status');
        location.reload();
      });
    }

    function showReplyForm(feedbackId) {
      const message = prompt('Enter your reply:');
      
      if (message && message.trim()) {
        const formData = new FormData();
        formData.append('feedback_id', feedbackId);
        formData.append('message', message);

        fetch('reply.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(result => {
          alert('Reply posted!');
          location.reload();
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Failed to post reply');
        });
      }
    }

    function deleteFeedback(feedbackId) {
      if (!confirm('Delete this feedback?')) return;

      const formData = new FormData();
      formData.append('id', feedbackId);

      fetch('deletefeedback.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(result => {
        alert('Deleted!');
        location.reload();
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete feedback');
      });
    }