// ===== Debt & Installment Management System - JS =====
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

// تأكيد قبل الإجراءات الخطرة
document.addEventListener('submit', (e) => {
  const form = e.target;
  if (form.dataset.confirm) {
    if (!confirm(form.dataset.confirm)) e.preventDefault();
  }
});

// تبديل ظهور حقول الأقساط
document.querySelectorAll('[data-toggle-installment]').forEach(el => {
  const target = document.querySelector(el.dataset.toggleInstallment);
  const update = () => {
    if (!target) return;
    target.style.display = el.value === 'installment' ? 'block' : 'none';
  };
  el.addEventListener('change', update);
  update();
});

// AJAX بحث الزبائن (Live)
const liveSearch = document.querySelector('#liveSearch');
if (liveSearch) {
  let t;
  liveSearch.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => {
      const q = liveSearch.value.trim();
      const url = new URL(window.location.href);
      url.searchParams.set('q', q);
      window.location = url.toString();
    }, 500);
  });
}

// AJAX: تحديث المبلغ المتبقي عند تغيير مبلغ الدفعة
const amountInput = document.querySelector('#paymentAmount');
const remainingDisplay = document.querySelector('#remainingDisplay');
if (amountInput && remainingDisplay) {
  const max = parseFloat(remainingDisplay.dataset.max || '0');
  amountInput.addEventListener('input', () => {
    const v = parseFloat(amountInput.value || '0');
    remainingDisplay.textContent = (max - v).toLocaleString('ar-EG');
    if (v > max) amountInput.classList.add('is-invalid');
    else amountInput.classList.remove('is-invalid');
  });
}
