(function () {
  // -------------------------
  // Table Search (client-side)
  // -------------------------
  const searchInput = document.getElementById("tableSearch");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const q = (this.value || "").toLowerCase();
      const rows = document.querySelectorAll("table tbody tr");
      rows.forEach((tr) => {
        const txt = tr.innerText.toLowerCase();
        tr.style.display = txt.includes(q) ? "" : "none";
      });
    });
  }

  // -------------------------
  // Bulk Paid: collect selected ids into hidden input
  // -------------------------
  const bulkBtn = document.getElementById("btnBulkPaid");
  const bulkForm = document.getElementById("bulkPaidForm");
  const bulkHidden = document.getElementById("bulkPaidIds");

  if (bulkBtn && bulkForm && bulkHidden) {
    bulkBtn.addEventListener("click", function () {
      const checks = document.querySelectorAll('input[name="bulk_paid[]"]:checked');
      const ids = Array.from(checks).map((c) => c.value);
      if (!ids.length) {
        alert("Select at least one voucher.");
        return;
      }
      bulkHidden.value = ids.join(",");
      bulkForm.submit();
    });
  }

  // -------------------------
  // Subtle click "pulse" effect for buttons
  // -------------------------
  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".btn");
    if (!btn) return;

    btn.animate(
      [
        { transform: "translateY(0px) scale(1)" },
        { transform: "translateY(-1px) scale(1.01)" },
        { transform: "translateY(0px) scale(1)" },
      ],
      { duration: 180, easing: "ease-out" }
    );
  });
})();