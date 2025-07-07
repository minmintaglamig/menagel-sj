<?php
session_start();

if (isset($_GET['error']) && $_GET['error'] === 'incomplete') {
    $_SESSION['popup'] = [
        'type' => 'error',
        'msg' => 'Please ensure all fields are filled in and files are uploaded.'
    ];
    header("Location: applicationform.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" href="pictures/logo.png" type= "image">
  <link rel="stylesheet" href="css/application.css" />
  <link rel="stylesheet" href="css/popup.css" />
  <script src="javascript/location.js"></script>
  <script src="javascript/popup.js"></script>
  <title>Application Form</title>
</head>
<body>
<?php

if (isset($_SESSION['popup'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    showPopup('<?= $_SESSION['popup']['type'] ?>', '<?= $_SESSION['popup']['msg'] ?>');
  });
</script>

<?php unset($_SESSION['popup']); endif; ?>

<div id="popup-container" class="popup-container"></div>
  <form id="applicationForm" method="POST" action="includes/applicationrec.php" enctype="multipart/form-data">
    <h1>Application Form</h1>
    <p>Kindly fill out this form, and we'll get back to you as soon as possible.</p>

    <div class="reminder-container">
      <button type="button" class="reminder-toggle">Reminders ▼</button>
      <div class="reminder-content">
        <p><i>📍 Fields with an asterisk (*) are required.</i></p>
        <p><i>📍 Provide a valid and active email address for updates.</i></p>
        <p><i>📍 Proof of billing must be a Meralco bill; otherwise, the application will not be approved.</i></p>
        <p><i>📍 A valid ID is required for identity verification.</i></p>
      </div>
    </div>

    <label>First Name *:</label>
    <input type="text" name="fname" placeholder="First Name" required>

    <label>Middle Name :</label>
    <input type="text" name="mname" placeholder="Middle Name">

    <label>Last Name *:</label>
    <input type="text" name="lname" placeholder="Last Name" required>

    <label>Complete Address *:</label>
    <select name="city" required>
      <option value="">Select City</option>
      <option value="Biñan City">Biñan City</option>
      <option value="Cabuyao City">Cabuyao City</option>
      <option value="Calamba City">Calamba City</option>
      <option value="San Pablo City">San Pablo City</option>
      <option value="San Pedro City">San Pedro City</option>
      <option value="Santa Rosa City">Santa Rosa City</option>
    </select>

    <select name="barangay" id="barangay" required>
      <option value="">Select Barangay</option>
    </select>

    <input type="text" name="street" placeholder="Street Address" required>

    <label>Resident Type *:</label>
    <small><i>"Owner" if you are the property owner. "Rental" if you are a tenant or leaseholder.</i></small>
    <select name="residenttype" required>
      <option value="Owner">Owner</option>
      <option value="Rental">Rental</option>
    </select>

    <label>Email *:</label>
    <input type="email" name="email" placeholder="Email Address" required>

    <label>Mobile Number *:</label>
    <input type="tel" name="mobile" placeholder="Mobile Number" required maxlength="11" pattern="\d{11}" title="Please enter a valid 11-digit mobile number" id="mobile">

    <label>Proof of Billing (Upload Image) *:</label>
    <input type="file" name="billing_proof" accept="image/*" required>
    <small style="color: red;"><i>*Please re-upload after page reload</i></small>

    <label>Valid ID (Upload Image) *:</label>
    <input type="file" name="valid_id" accept="image/*" required>
    <small style="color: red;"><i>*Please re-upload after page reload</i></small>

    <label>Promo ID *:</label>
    <select name="promo_id" required>
      <option value="1">Unli Plan 800 (Up to 20 Mbps)</option>
      <option value="2">Unli Plan 1000 (Up to 40 Mbps)</option>
      <option value="3">Unli Plan 1500 (Up to 70 Mbps)</option>
      <option value="4">Unli Plan 2000 (Up to 100 Mbps)</option>
    </select>

    <button type="button" id="submitButton">Review Submission</button>
  </form>

  <?php include('faq_widget.php'); ?>

  <div id="summaryModal" class="modal">
    <div class="modal-content">
      <h2>Confirm your submission</h2>
      <div id="summaryContent"></div>
      <div class="flex">
        <button class="back-button">Back</button>
        <button class="confirm-button">Confirm and Submit</button>
      </div>
    </div>
  </div>

  <div class="terminal-loader" id="loadingTerminal">
    <div class="terminal-header">
      <span class="terminal-title">Status</span>
      <span class="terminal-controls">
        <span class="control close"></span>
        <span class="control minimize"></span>
        <span class="control maximize"></span>
      </span>
    </div>
    <div class="text">Sending email....</div>
  </div>

  <script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById("applicationForm");
  const summaryModal = document.getElementById("summaryModal");
  const summaryContent = document.getElementById("summaryContent");
  const backButton = document.querySelector(".back-button");
  const confirmButton = document.querySelector(".confirm-button");

  const inputs = form.querySelectorAll("input:not([type='file']), select");

  inputs.forEach(input => {
    const saved = localStorage.getItem(input.name);
    if (saved !== null) input.value = saved;
  });

  inputs.forEach(input => {
    input.addEventListener("input", () => {
      localStorage.setItem(input.name, input.value);
    });
  });

  const savedCity = localStorage.getItem("city");
  const savedBarangay = localStorage.getItem("barangay");

  if (savedCity) {
    const citySelect = document.querySelector("select[name='city']");
    citySelect.value = savedCity;
    const changeEvent = new Event('change');
    citySelect.dispatchEvent(changeEvent);

    const brgyInterval = setInterval(() => {
      const brgySelect = document.getElementById("barangay");
      const exists = Array.from(brgySelect.options).some(
        opt => opt.value === savedBarangay
      );
      if (exists) {
        brgySelect.value = savedBarangay;
        clearInterval(brgyInterval);
      }
    }, 200);
  }

  const barangaySelect = document.querySelector('select[name="barangay"]');
  barangaySelect.addEventListener("change", () => {
    localStorage.setItem("barangay", barangaySelect.value);
  });

  const emailInput = form.querySelector('input[name="email"]');
  if (emailInput) {
    emailInput.addEventListener("input", () => {
      localStorage.setItem("prefill_signup_email", emailInput.value);
    });

    if (emailInput.value) {
      localStorage.setItem("prefill_signup_email", emailInput.value);
    }
  }

  document.getElementById("mobile").addEventListener("input", (e) => {
    e.target.value = e.target.value.replace(/\D/g, "").slice(0, 11);
  });

  document.getElementById("submitButton").addEventListener("click", (event) => {
    event.preventDefault();

    const formData = new FormData(form);

    const requiredFields = [
      "fname", "lname", "street", "barangay", "city", "residenttype",
      "email", "mobile", "promo_id"
    ];

    const isValid = requiredFields.every(name => {
      const value = formData.get(name);
      return value && value.trim() !== "";
    });

    const billingProof = form.querySelector('input[name="billing_proof"]').files[0];
    const validId = form.querySelector('input[name="valid_id"]').files[0];

    if (!isValid || !billingProof || !validId) {
  window.location.href = "applicationform.php?error=incomplete";
  return;
}

    const get = (name) => formData.get(name)?.trim();
    const promoText = form.querySelector("select[name='promo_id']").selectedOptions[0]?.textContent;
    const address = [get("street"), get("barangay"), get("city")].filter(Boolean).join(", ");

    const summaryHTML = `
      <p><strong>Full Name:</strong> ${get("fname")} ${get("mname")} ${get("lname")}</p>
      <p><strong>Address:</strong> ${address}</p>
      <p><strong>Resident Type:</strong> ${get("residenttype")}</p>
      <p><strong>Email:</strong> ${get("email")}</p>
      <p><strong>Mobile:</strong> ${get("mobile")}</p>
      <p><strong>Promo Selected:</strong> ${promoText}</p>
      <div class="image-summary">
        <div><strong>Billing Proof:</strong><br><img src="${URL.createObjectURL(billingProof)}" width="150"/></div>
        <div><strong>Valid ID:</strong><br><img src="${URL.createObjectURL(validId)}" width="150"/></div>
      </div>
    `;
    summaryContent.innerHTML = summaryHTML;
    summaryModal.style.display = "block";
  });

  backButton.addEventListener("click", () => {
    summaryModal.style.display = "none";
  });

  confirmButton.addEventListener("click", () => {
    summaryModal.style.display = "none";
    document.getElementById("loadingTerminal").style.display = "block";
    localStorage.clear();
    setTimeout(() => {
      form.submit();
    }, 1000);
  });

  const reminderToggle = document.querySelector(".reminder-toggle");
  const reminderContent = document.querySelector(".reminder-content");

  reminderToggle.addEventListener("click", () => {
    reminderContent.style.display =
      reminderContent.style.display === "block" ? "none" : "block";
  });
});
</script>

</body>
</html>