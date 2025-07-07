document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll("form").forEach(form => {
        let submitButton = form.querySelector("button[type='submit']");
        if (!submitButton) return;

        let originalValues = {};
        
        form.querySelectorAll("input, textarea, select").forEach(input => {
            originalValues[input.name] = input.value;
            input.addEventListener("input", () => checkChanges(form, submitButton, originalValues));
        });

        function checkChanges(form, button, originalValues) {
            let changed = false;
            form.querySelectorAll("input, textarea, select").forEach(input => {
                if (input.type === "file" && input.files.length > 0) {
                    changed = true;
                } else if (input.value !== originalValues[input.name]) {
                    changed = true;
                }
            });
            button.disabled = !changed;
        }
    });
});