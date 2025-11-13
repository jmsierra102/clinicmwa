<?php
require_once 'db.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Page router
$role = $_SESSION['role'];
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="logo">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"></path></svg>
            <span>VetClinic</span>
        </div>
        <div class="user-info">
            Welcome, <strong><?php echo e($_SESSION["firstname"]); ?></strong>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <nav>
        <ul>
            <li><a href="?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
            <?php if ($role == 'admin'): ?>
                <li><a href="?page=appointments" class="<?php echo $page == 'appointments' ? 'active' : ''; ?>">Appointments</a></li>
                <li><a href="?page=veterinarians" class="<?php echo $page == 'veterinarians' ? 'active' : ''; ?>">Veterinarians</a></li>
                <li><a href="?page=pets" class="<?php echo $page == 'pets' ? 'active' : ''; ?>">Pets</a></li>
                <li><a href="?page=clients" class="<?php echo $page == 'clients' ? 'active' : ''; ?>">Clients</a></li>
            <?php else: // Client ?>
                <li><a href="?page=my_appointments" class="<?php echo $page == 'my_appointments' ? 'active' : ''; ?>">My Appointments</a></li>
                <li><a href="?page=my_pets" class="<?php echo $page == 'my_pets' ? 'active' : ''; ?>">My Pets</a></li>
                <li><a href="?page=book_appointment" class="<?php echo $page == 'book_appointment' ? 'active' : ''; ?>">Book Appointment</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container">
        <?php
        // Dynamically include page content based on role and page
        if ($role == 'admin') {
            $page_path = "admin/{$page}.php";
        } else {
            $page_path = "client/{$page}.php";
        }

        if (file_exists($page_path)) {
            include $page_path;
        } else {
            // Fallback to a default dashboard if the page doesn't exist
            if ($role == 'admin') {
                include 'admin/dashboard.php';
            } else {
                include 'client/dashboard.php';
            }
        }
        ?>
    </div>

    <script>
        // Add frontend validation for appointment date
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.querySelector('input[name="appointment_date"]');
            if (dateInput) {
                const now = new Date();
                const today = now.toISOString().split('T')[0];
                
                now.setMonth(now.getMonth() + 2);
                const maxDate = now.toISOString().split('T')[0];

                dateInput.setAttribute('min', today);
                dateInput.setAttribute('max', maxDate);
            }
        });
    </script>

</body>
</html>