// ====== Get form and input elements ======
const form              = document.getElementById("signupForm");
const usernameInput     = document.getElementById("signupUsername");
const emailInput        = document.getElementById("signupEmail");
const passwordInput     = document.getElementById("signupPassword");
const confirmInput      = document.getElementById("signupConfirm");
const accountTypeSelect = document.getElementById("accountType");
const termsCheckbox     = document.getElementById("terms");

// NEW
const genderSelect = document.getElementById("gender");
const birthInput   = document.getElementById("birthdate");
const addressInput = document.getElementById("address");

// ====== Status elements ======
const usernameStatus = document.getElementById("UsernameStatus");
const emailStatus    = document.getElementById("EmailStatus");
const passwordStatus = document.getElementById("PasswordStatus");
const confirmStatus  = document.getElementById("ConfirmStatus");
const formStatus     = document.getElementById("signupStatus");

// NEW
const genderStatus  = document.getElementById("GenderStatus");
const birthStatus   = document.getElementById("BirthStatus");
const addressStatus = document.getElementById("AddressStatus");

// ===================== Username =====================
usernameInput.addEventListener("keyup", () => {
  const username = usernameInput.value.trim();

  if (!username) {
    usernameStatus.textContent = "Please enter a username.";
    usernameStatus.className   = "status error";
  } else if (username.length < 3) {
    usernameStatus.textContent = "Username must be at least 3 characters.";
    usernameStatus.className   = "status error";
  } else {
    usernameStatus.textContent = "✓ Valid username.";
    usernameStatus.className   = "status success";
  }
});

// ===================== Email =====================
emailInput.addEventListener("keyup", () => {
  const email       = emailInput.value.trim();
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!email) {
    emailStatus.textContent = "Please enter an email address.";
    emailStatus.className   = "status error";
  } else if (!emailPattern.test(email)) {
    emailStatus.textContent = "Please enter a valid email address.";
    emailStatus.className   = "status error";
  } else {
    emailStatus.textContent = "✓ Valid email.";
    emailStatus.className   = "status success";
  }
});

// ===================== Password =====================
passwordInput.addEventListener("keyup", () => {
  const password = passwordInput.value;

  if (!password) {
    passwordStatus.textContent = "Please enter a password.";
    passwordStatus.className   = "status error";
  } else if (password.length < 8) {
    passwordStatus.textContent = "Password must be at least 8 characters.";
    passwordStatus.className   = "status error";
  } else {
    passwordStatus.textContent = "✓ Strong password.";
    passwordStatus.className   = "status success";
  }

  // re-check confirm if user already typed it
  if (confirmInput.value) {
    confirmInput.dispatchEvent(new Event("keyup"));
  }
});

// ===================== Confirm Password =====================
confirmInput.addEventListener("keyup", () => {
  const password        = passwordInput.value;
  const confirmPassword = confirmInput.value;

  if (!confirmPassword) {
    confirmStatus.textContent = "Please confirm your password.";
    confirmStatus.className   = "status error";
  } else if (confirmPassword !== password) {
    confirmStatus.textContent = "Passwords do not match.";
    confirmStatus.className   = "status error";
  } else {
    confirmStatus.textContent = "✓ Passwords match.";
    confirmStatus.className   = "status success";
  }
});

// ===================== Account Type =====================
accountTypeSelect.addEventListener("change", () => {
  const accountType = accountTypeSelect.value;

  if (!accountType) {
    formStatus.textContent = "Please select an account type.";
    formStatus.className   = "status error";
  } else {
    formStatus.textContent =
      `✓ Account type: ${accountType.charAt(0).toUpperCase() + accountType.slice(1)} selected.`;
    formStatus.className   = "status success";
  }
});

// ===================== Gender =====================
if (genderSelect) {
  genderSelect.addEventListener("change", () => {
    const gender = genderSelect.value;

    if (!gender) {
      genderStatus.textContent = "Please select your gender.";
      genderStatus.className   = "status error";
    } else {
      genderStatus.textContent = "✓ Gender selected.";
      genderStatus.className   = "status success";
    }
  });
}

// ===================== Birthdate =====================
if (birthInput) {
  birthInput.addEventListener("change", () => {
    const birthdate = birthInput.value;

    if (!birthdate) {
      birthStatus.textContent = "Please choose your birth date.";
      birthStatus.className   = "status error";
      return;
    }

    // optional: simple age check (13+)
    const today    = new Date();
    const bDate    = new Date(birthdate);
    let age        = today.getFullYear() - bDate.getFullYear();
    const m        = today.getMonth() - bDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < bDate.getDate())) age--;

    if (age < 13) {
      birthStatus.textContent = "You must be at least 13 years old.";
      birthStatus.className   = "status error";
    } else {
      birthStatus.textContent = `✓ Age: ${age} years.`;
      birthStatus.className   = "status success";
    }
  });
}

// ===================== Address =====================
if (addressInput) {
  addressInput.addEventListener("keyup", () => {
    const address = addressInput.value.trim();

    if (!address) {
      addressStatus.textContent = "Please enter your address.";
      addressStatus.className   = "status error";
    } else if (address.length < 5) {
      addressStatus.textContent = "Address is too short.";
      addressStatus.className   = "status error";
    } else {
      addressStatus.textContent = "✓ Address looks good.";
      addressStatus.className   = "status success";
    }
  });
}

// ===================== Submit =====================
form.addEventListener("submit", function (e) {
  const username        = usernameInput.value.trim();
  const email           = emailInput.value.trim();
  const password        = passwordInput.value;
  const confirmPassword = confirmInput.value;
  const role            = accountTypeSelect.value;
  const termsAccepted   = termsCheckbox.checked;

  const gender    = genderSelect ? genderSelect.value : "";
  const birthdate = birthInput ? birthInput.value : "";
  const address   = addressInput ? addressInput.value.trim() : "";

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  formStatus.textContent = "";
  formStatus.className   = "status";

  // basic required fields
  if (!username || !email || !password || !confirmPassword || !role ||
      !gender || !birthdate || !address) {
    e.preventDefault();
    formStatus.textContent = "Please fill in all fields.";
    formStatus.className   = "status error";
    return;
  }

  if (username.length < 3) {
    e.preventDefault();
    formStatus.textContent = "Username must be at least 3 characters.";
    formStatus.className   = "status error";
    return;
  }

  if (!emailPattern.test(email)) {
    e.preventDefault();
    formStatus.textContent = "Please enter a valid email address.";
    formStatus.className   = "status error";
    return;
  }

  if (password.length < 8) {
    e.preventDefault();
    formStatus.textContent = "Password must be at least 8 characters.";
    formStatus.className   = "status error";
    return;
  }

  if (password !== confirmPassword) {
    e.preventDefault();
    formStatus.textContent = "Passwords do not match.";
    formStatus.className   = "status error";
    return;
  }

  if (!termsAccepted) {
    e.preventDefault();
    formStatus.textContent = "You must accept the terms and conditions.";
    formStatus.className   = "status error";
    return;
  }

  // Passed all checks
  formStatus.textContent = "Creating account...";
  formStatus.className   = "status success";
  // form سيكمل إرسال البيانات للـ PHP عادي
});
