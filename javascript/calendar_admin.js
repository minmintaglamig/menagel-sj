document.addEventListener("DOMContentLoaded", function () {
    const calendarBody = document.getElementById("calendar-body");
    const monthYear = document.getElementById("month-year");
    const prevMonthBtn = document.getElementById("prev-month");
    const nextMonthBtn = document.getElementById("next-month");

    let currentDate = new Date();

    function formatTime(timeStr) {
        let [hours, minutes] = timeStr.split(":");
        hours = parseInt(hours);
        let period = hours >= 12 ? "P.M." : "A.M.";
        hours = hours % 12 || 12;
        return `${hours}:${minutes} ${period}`;
    }

    function renderCalendar() {
        calendarBody.innerHTML = "";
        let firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        let lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
        monthYear.textContent = firstDay.toLocaleString("default", { month: "long", year: "numeric" });

        for (let i = 1; i <= lastDay.getDate(); i++) {
            let dayDiv = document.createElement("div");
            dayDiv.classList.add("calendar-day");
            dayDiv.textContent = i;

            let dateStr = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, "0")}-${String(i).padStart(2, "0")}`;

            let event = appointments.find((app) => app.schedule_date === dateStr);
            if (event) {
                let eventDiv = document.createElement("div");
                eventDiv.classList.add("event");
                eventDiv.textContent = "📌 " + event.client_name;
                dayDiv.appendChild(eventDiv);

                eventDiv.addEventListener("mouseover", function () {
                    let stickyNote = document.createElement("div");
                    stickyNote.classList.add("sticky-note");
                    let formattedTime = formatTime(event.schedule_time);
                    stickyNote.textContent = `${event.client_name} ${formattedTime}`;
                    document.body.appendChild(stickyNote);
                    stickyNote.style.top = eventDiv.getBoundingClientRect().top + "px";
                    stickyNote.style.left = eventDiv.getBoundingClientRect().left + "px";
                });

                eventDiv.addEventListener("mouseleave", function () {
                    document.querySelectorAll(".sticky-note").forEach((note) => note.remove());
                });
            }

            calendarBody.appendChild(dayDiv);
        }
    }

    prevMonthBtn.addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextMonthBtn.addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    renderCalendar();
});