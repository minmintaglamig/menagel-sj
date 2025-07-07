<!DOCTYPE html>
<html>
<head>
  <title>AES-256 Step-by-Step Demo</title>
  <style>
    body { font-family: monospace; background: #1e1e2f; color: #f0f0f0; padding: 20px; }
    input, textarea { width: 100%; padding: 10px; font-family: monospace; background: #2a2a3d; color: #fff; border: 1px solid #444; margin-top: 10px; }
    .step-box { margin-top: 20px; background: #2f2f47; padding: 15px; border-radius: 8px; }
    .byte { display: inline-block; width: 30px; text-align: center; margin: 2px; padding: 5px; border-radius: 4px; background: #444; }
    .round-step { margin-top: 15px; display: none; }
    .round-step.active { display: block; animation: fadeIn 0.7s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
  </style>
</head>
<body>
  <h1>AES-256 Demo (16-Char Block)</h1>
  <form method="post">
    <label>Enter Exactly 16 Characters of Plaintext:</label>
    <input type="text" name="plaintext" pattern=".{16}" maxlength="16" required>

    <label>Enter 64-char Hex Key (256-bit AES key):</label>
    <input type="text" name="key" pattern="[a-fA-F0-9]{64}" required>

    <button type="submit">Encrypt</button>
  </form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $plaintext = $_POST['plaintext'];
  $hexKey = $_POST['key'];

  $key = hex2bin($hexKey);
  $iv = str_repeat("\0", 16); // fixed IV for demo simplicity

  $blocks = str_split($plaintext);
  $output = "<div class='step-box'><strong>Step 1: Initial Plaintext Bytes</strong><br>";
  foreach ($blocks as $char) {
    $output .= "<span class='byte'>" . strtoupper(bin2hex($char)) . "</span> ";
  }
  $output .= "</div>";

  // Round simulation (not actual cryptographic steps)
  for ($round = 0; $round <= 14; $round++) {
    $output .= "<div class='round-step'><strong>Round $round:</strong><br>";
    foreach ($blocks as $i => $char) {
      $xorChar = ord($char) ^ ord($key[$i % strlen($key)]); // simulated effect
      $blocks[$i] = chr($xorChar);
      $output .= "<span class='byte'>" . strtoupper(bin2hex($blocks[$i])) . "</span> ";
    }
    $output .= "</div>";
  }
  echo bin2hex(openssl_random_pseudo_bytes(32)); // 64-char hex key

  echo $output;
  echo "<script>
    let steps = document.querySelectorAll('.round-step');
    let i = 0;
    steps[0].classList.add('active');
    setInterval(() => {
      if (i < steps.length - 1) {
        steps[i].classList.remove('active');
        steps[++i].classList.add('active');
      }
    }, 1500);
  </script>";
}
?>
</body>
</html>