<?php
    session_start();
    include('includes/dbh.php');
    include('includes/encryption.php');
    
    if (!isset($_SESSION['user_email'])) {
        $_SESSION['popup'] = ['type' => 'alert', 'msg' => 'Unauthorized access! Please log in.'];
        header("Location: login.php");
        exit();
    }       
    
    $ads_query = "SELECT * FROM tbladvertisement ORDER BY created_at DESC";
    $ads_result = $conn->query($ads_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="pictures/logo.png" type= "image">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/toggle.css">
    <link rel="stylesheet" href="css/advertisement.css">
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/script.js"></script>
    <script defer src="javascript/popup.js"></script>
    <title>Manage Advertisements</title>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            width: 300px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .modal-content button {
            margin: 10px 5px;
            padding: 8px 15px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            transition: background-color 0.3s;
        }

        .modal-content button:hover {
            background-color: #45a049;
        }

        .modal-content button.cancel {
            background-color: #f44336;
        }

        .modal-content button.cancel:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>

<?php
if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
    exit();
}
if (isset($_SESSION['popup'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    showPopup('<?= $_SESSION['popup']['type'] ?>', '<?= $_SESSION['popup']['msg'] ?>');
  });
</script>
<?php unset($_SESSION['popup']); endif; ?>
<div id="popup-container" class="popup-container"></div>

<?php include('header.php'); ?>
<div class="main-container">
    <?php include('admin_sidebar.html'); ?>
</div>
<h1>Manage Advertisements</h1>

<div class="ad-form-wrapper">
    <form action="includes/process_advertisement.php" method="POST" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Content:</label>
        <textarea name="content" required></textarea>

        <label>Upload Image:</label>
        <input type="file" name="image" accept="image/*" required>

        <button type="submit" name="add_ad">Post Advertisement</button>
    </form>
</div>

<h2>Existing Advertisements</h2>
<table>
    <tr>
        <th>Image</th>
        <th>Title</th>
        <th>Content</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php while ($ad = $ads_result->fetch_assoc()): ?>
        <tr>
            <td><img src="uploads/advertisement/<?= htmlspecialchars($ad['image']) ?>" alt="Ad Image"></td>
            <td><strong><?= htmlspecialchars($ad['title']) ?></strong></td>
            <td><i><?= htmlspecialchars($ad['content']) ?></i></td>
            <td><?= $ad['is_visible'] ? 'Visible' : 'Hidden' ?></td>
            <td>
                <div class="action-buttons">
                    <form action="includes/process_advertisement.php" method="POST" style="display:inline;">
                        <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                        <button type="submit" name="toggle_visibility">
                            <?= $ad['is_visible'] ? 'Hide' : 'Show' ?>
                        </button>
                    </form>
                    <form action="includes/process_advertisement.php" method="POST" style="display:inline;">
                        <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                        <button type="button" onclick="confirmDelete(<?= $ad['id'] ?>)">Delete</button>
                    </form>
                    <form action="edit_advertisement.php" method="GET" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $ad['id'] ?>">
                        <button type="submit">Edit</button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<div id="deleteModal" class="modal" style="display:none;">
    <div class="modal-content">
        <p>Are you sure you want to delete this advertisement?</p>
        <input type="hidden" name="ad_id" id="deleteAdId">
        <button type="button" onclick="deleteAd()">Yes, Delete</button>
        <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
    </div>
</div>

<script>
function deleteAd() {
    const adId = document.getElementById('deleteAdId').value;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'includes/process_advertisement.php';

    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'ad_id';
    idInput.value = adId;

    const deleteInput = document.createElement('input');
    deleteInput.type = 'hidden';
    deleteInput.name = 'delete_ad';
    deleteInput.value = '1';

    form.appendChild(idInput);
    form.appendChild(deleteInput);

    document.body.appendChild(form);
    form.submit();
}

function confirmDelete(adId) {
    document.getElementById('deleteAdId').value = adId;
    document.getElementById('deleteModal').style.display = 'flex'; // Show modal
}

function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
}


</script>
<script src="javascript/sidebar-toggle.js"></script>
</body>
</html>