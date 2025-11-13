<?php
require_once 'db.php';

$error = '';
$success = '';

// Show success message on redirect from signup
if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
    $success = 'Registration successful! You can now log in.';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["email"])) || empty(trim($_POST["password"]))) {
        $error = 'Please enter email and password.';
    } else {
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        // Prepare a select statement
        $sql = "SELECT id, firstname, lastname, password, role FROM users WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $firstname, $lastname, $hashed_password, $role);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            // session_start(); // Already started in db.php

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["firstname"] = $firstname;
                            $_SESSION["lastname"] = $lastname;
                            $_SESSION["role"] = $role;

                            // Redirect user to the main page
                            header("location: index.php");
                            exit;
                        } else {
                            // Display an error message if password is not valid
                            $error = 'The password you entered was not valid.';
                        }
                    }
                } else {
                    $error = 'No account found with that email.';
                }
            } else {
                $error = 'Oops! Something went wrong. Please try again later.';
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vet Clinic</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="logo">🐾</div>
            <h2>Welcome Back!</h2>
            <p>Log in to manage your appointments.</p>
        </div>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>    
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Log In</button>
            </div>
        </form>
        <div class="auth-footer">
            <p>Don't have an account? <a href="signup.php">Sign up now</a>.</p>
        </div>
    </div>    
</body>
</html>
