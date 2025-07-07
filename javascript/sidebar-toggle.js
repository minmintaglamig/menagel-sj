document.addEventListener("DOMContentLoaded", function () {
  const menuBtn = document.querySelector(".menu-btn");
  const sidebar = document.querySelector(".sidebar");

  console.log("🔎 Menu Button:", menuBtn);
  console.log("🔎 Sidebar:", sidebar);

  if (!menuBtn || !sidebar) {
      console.error("❌ Menu button or sidebar not found!");
      return;
  }

  menuBtn.addEventListener("click", function () {
      console.log("✅ Menu button clicked!");

      sidebar.classList.toggle("expanded");

      console.log("📌 Sidebar Expanded:", sidebar.classList.contains("expanded"));
  });
});