function showPopup(type, message) {
  const popupContainer = document.getElementById("popup-container");

  const popup = document.createElement("div");
  popup.classList.add("popup", `${type}-popup`);

  const iconSVG = {
    success: '<svg viewBox="0 0 20 20"><path d="M7.629 13.29l-3.3-3.3-1.414 1.415 4.714 4.714L17.5 6.257l-1.414-1.414z"/></svg>',
    error: '<svg viewBox="0 0 20 20"><path d="M10 8.586l4.95-4.95 1.414 1.414L11.414 10l4.95 4.95-1.414 1.414L10 11.414l-4.95 4.95-1.414-1.414L8.586 10 3.636 5.05l1.414-1.414z"/></svg>',
    alert: '<svg viewBox="0 0 20 20"><path d="M10 0a10 10 0 100 20A10 10 0 0010 0zm1 15H9v-2h2v2zm0-4H9V5h2v6z"/></svg>',
    info: '<svg viewBox="0 0 20 20"><path d="M10 0C4.485 0 0 4.486 0 10s4.485 10 10 10 10-4.486 10-10S15.515 0 10 0zm1 15H9v-6h2v6zm0-8H9V5h2v2z"/></svg>',
  };

  popup.innerHTML = `
    <div class="popup-icon ${type}-icon">${iconSVG[type]}</div>
    <div class="${type}-message">${message}</div>
    <div class="popup-icon close-icon" onclick="this.parentElement.remove()">
      <svg viewBox="0 0 20 20"><path d="M10 8.586l4.95-4.95 1.414 1.414L11.414 10l4.95 4.95-1.414 1.414L10 11.414l-4.95 4.95-1.414-1.414L8.586 10 3.636 5.05l1.414-1.414z"/></svg>
    </div>
  `;

  popupContainer.appendChild(popup);

  setTimeout(() => {
    popup.remove();
  }, 4000);
}

function setupDeleteModals() {
  const modal = document.getElementById("deleteModal");
  const confirmDelete = document.getElementById("confirmDelete");
  const cancelDelete = document.getElementById("cancelDelete");
  const deleteBtns = document.querySelectorAll(".delete-btn");

  if (!modal || !confirmDelete || !cancelDelete || deleteBtns.length === 0) return;

  deleteBtns.forEach((btn) => {
    btn.addEventListener("click", function (event) {
      event.preventDefault();
      const staffId = this.getAttribute("data-id");

      modal.style.display = "block";

      confirmDelete.onclick = function () {
        window.location.href = `staff_management.php?delete=${staffId}`;
      };

      cancelDelete.onclick = function () {
        modal.style.display = "none";
      };
    });
  });

  window.addEventListener("click", function (event) {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  });
}