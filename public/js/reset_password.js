// ../../public/js/reset_password.js



  const form = document.getElementById("passwordForm");
  const passInput = document.getElementById("new_password");
  const conpassInput = document.getElementById("confirm_password");

  const passwordStatus = document.getElementById("PasswordStatus");
  const confirmStatus = document.getElementById("ConfirmStatus");
  const formStatus = document.getElementById("signupStatus");


  // ====== live validation for password ======
  passInput.addEventListener("keyup", () => {
    const pass = passInput.value;

    if (!pass) {
      passwordStatus.textContent = "Please enter a password.";
      passwordStatus.className = "status error";
    } else if (pass.length < 8) {
      passwordStatus.textContent = "Password must be at least 8 characters.";
      passwordStatus.className = "status error";
    } else {
      passwordStatus.textContent = "Password is valid.";
      passwordStatus.className = "status success";
    }
  });

  // ====== live validation for confirm password ======
  conpassInput.addEventListener("keyup", () => {
    const pass = passInput.value;
    const conpass = conpassInput.value;

    if (!conpass) {
      confirmStatus.textContent = "Please confirm your password.";
      confirmStatus.className = "status error";
    } else if (conpass.length < 8) {
      confirmStatus.textContent = "Password must be at least 8 characters.";
      confirmStatus.className = "status error";
    } else if (conpass !== pass) {
      confirmStatus.textContent = "Passwords do not match.";
      confirmStatus.className = "status error";
    } else {
      confirmStatus.textContent = "Passwords match.";
      confirmStatus.className = "status success";
    }
  });

  // ====== on submit ======
  form.addEventListener("submit", (e) => {
    const pass = passInput.value;
    const conpass = conpassInput.value;

    formStatus.textContent = "";
    formStatus.className = "status";

    if (!pass || !conpass) {
      e.preventDefault();
      formStatus.textContent = "Please fill in both password fields.";
      formStatus.className = "status error";
      return;
    }

    if (pass.length < 8 || conpass.length < 8) {
      e.preventDefault();
      formStatus.textContent = "Password must be at least 8 characters.";
      formStatus.className = "status error";
      return;
    }

    if (pass !== conpass) {
      e.preventDefault();
      formStatus.textContent = "Passwords do not match.";
      formStatus.className = "status error";
      return;
    }

    // لو كل شيء تمام، نخلي الفورم يروح لـ PHP
    formStatus.textContent = "Resetting password...";
    formStatus.className = "status success";
  });
