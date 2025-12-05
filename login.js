// ../FrontOffice/login.js

document.addEventListener("DOMContentLoaded", () => {
  const form   = document.getElementById("loginForm");
  const emailInput = document.querySelector("input[name='email']");
  const passInput  = document.querySelector("input[name='password']");
  const statusDiv  = document.getElementById("status");

  // Regex بسيط لفحص شكل الإيميل
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  form.addEventListener("submit", (e) => {
    // نمسح الرسالة القديمة
    statusDiv.textContent = "";
    statusDiv.classList.remove("error", "success");

    const email = emailInput.value.trim();
    const pass  = passInput.value;

    // 1) فحص الحقول الفارغة
    if (!email || !pass) {
      e.preventDefault();
      statusDiv.textContent = "Please fill in both email and password.";
      statusDiv.classList.add("error");
      return;
    }

    // 2) فحص شكل الإيميل
    if (!emailPattern.test(email)) {
      e.preventDefault();
      statusDiv.textContent = "Please enter a valid email address.";
      statusDiv.classList.add("error");
      return;
    }

    // 3) فحص طول الباسورد (اختياري)
    if (pass.length < 8) {
      e.preventDefault();
      statusDiv.textContent = "Password must be at least 6 characters.";
      statusDiv.classList.add("error");
      return;
    }

    // لو كل شيء تمام نخلي الفورم يكمّل إرسال عادي للـ PHP
    statusDiv.textContent = "Checking your account...";
    statusDiv.classList.add("success");
    // ما نعمل preventDefault -> يروح الطلب لـ login.php ويتحقق من الداتابيس
  });
});
