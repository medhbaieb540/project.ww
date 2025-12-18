// public/js/create_company.js

document.addEventListener("DOMContentLoaded", () => {
  const form         = document.querySelector("form");
  const nameInput    = document.querySelector("input[name='name']");
  const addressInput = document.querySelector("input[name='address']");
  const statusSelect = document.querySelector("select[name='status']");
  const descInput    = document.querySelector("textarea[name='description']");
  const statusBox    = document.getElementById("CompanyStatus");

  // دالة مساعدة لعرض رسالة جميلة فوق الزر
  function showMessage(msg, type = "error") {
    if (!statusBox) return;
    statusBox.textContent = msg;
    statusBox.classList.remove("error", "success");
    statusBox.classList.add(type);
  }

  form.addEventListener("submit", (e) => {
    const name    = nameInput.value.trim();
    const address = addressInput.value.trim();
    const status  = statusSelect.value;
    const desc    = descInput.value.trim();

    // نفضّي الرسالة القديمة
    showMessage("");

    // 1) فحص الحقول الفارغة
    if (!name || !address || !desc) {
      e.preventDefault();
      showMessage("Please fill in all fields (name, address, description).", "error");
      return;
    }

    // 2) طول الاسم
    if (name.length < 3) {
      e.preventDefault();
      showMessage("Company name must be at least 3 characters.", "error");
      return;
    }

    // 3) العنوان قصير جدًا؟
    if (address.length < 5) {
      e.preventDefault();
      showMessage("Please enter a more detailed address.", "error");
      return;
    }

    // 4) الوصف قصير جدًا؟
    if (desc.length < 10) {
      e.preventDefault();
      showMessage("Description should be at least 10 characters.", "error");
      return;
    }

    // 5) حالة الشركة (status) — عندك قيم جاهزة في select
    if (!status) {
      e.preventDefault();
      showMessage("Please choose a company status.", "error");
      return;
    }

    // لو كل شيء تمام نخلي الفورم يُرسل عادي
    showMessage("Creating company...", "success");
    // لا نعمل e.preventDefault -> الطلب يروح للـ PHP
  });
});
