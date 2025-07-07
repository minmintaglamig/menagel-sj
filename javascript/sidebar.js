document.addEventListener("DOMContentLoaded", function () {
    let sidebar = document.getElementById("sidebar");
    let dropdown = document.querySelector(".dropdown");
    let dropdownContent = document.querySelector(".dropdown-content");
    let dropdownToggle = document.querySelector(".dropdown-toggle");

    sidebar.addEventListener("mouseenter", function () {
        sidebar.classList.add("expanded");
    });

    sidebar.addEventListener("mouseleave", function () {
        if (!sidebar.classList.contains("clicked")) {
            sidebar.classList.remove("expanded");
            dropdown.classList.remove("active");
        }
    });

    sidebar.addEventListener("click", function () {
        sidebar.classList.toggle("clicked");
    });

    dropdownToggle.addEventListener("click", function (event) {
        event.preventDefault();
        dropdown.classList.toggle("active");
    });

    document.addEventListener("mousemove", function (event) {
        if (!sidebar.contains(event.target) && !dropdownContent.contains(event.target)) {
            dropdown.classList.remove("active");
        }
    });

    document.addEventListener("click", function (event) {
        if (!sidebar.contains(event.target) && sidebar.classList.contains("clicked")) {
            sidebar.classList.remove("expanded");
            sidebar.classList.remove("clicked");
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        let sidebarToggle = document.getElementById("sidebarToggle");
        if (sidebarToggle) {
            sidebarToggle.addEventListener("click", function () {
                document.getElementById("sidebar").classList.toggle("active");
            });
        }
    });    
});