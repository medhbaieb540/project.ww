 const form = document.getElementById('resetForm');
    const status = document.getElementById('resetStatus');

    form.addEventListener('submit', function (e) {
      

      const email = document.getElementById('resetEmail').value.trim();
      if (!email) {
        e.preventDefault();
        status.textContent = 'Please enter your email address.';
        status.className = 'status error';
        return;
      }

      // Basic email pattern check
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email)) {
        e.preventDefault();
        status.textContent = 'Please enter a valid email address.';
        status.className = 'status error';
        return;
      }

      status.textContent = 'Sending reset link...';
      status.className = 'status';

      // Demo: simulate server response
      setTimeout(() => {
        status.textContent = 'If an account with that email exists, a reset link has been sent.';
        status.className = 'status success';
      }, 800);
    });