<?php
require_once 'db.php';

// New password for the admin user
$new_password = 'password123';

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update the admin user's password in the database
$sql = "UPDATE users SET password = ? WHERE email = 'admin@clinic.com'";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $hashed_password);

    if ($stmt->execute()) {
        echo "Admin password has been reset successfully.<br>";
        echo "Your new password is: <strong>" . $new_password . "</strong><br>";
        echo "<p style='color:red;'>For security reasons, please delete this file (reset_admin_password.php) immediately.</p>";
    } else {
        echo "Error updating password: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
?>
