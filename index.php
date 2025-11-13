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
        <div class="header-left">
            <button id="sidebar-toggle" class="toggle-btn">
                <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"></path></svg>
            </button>
            <div class="logo">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"></path></svg>
                <span>VetClinic</span>
            </div>
        </div>
        <div class="user-info">
            Welcome, <strong><?php echo e($_SESSION["firstname"]); ?></strong>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <div class="main-layout">
        <nav>
            <ul>
                <li><a href="?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24"><path d="M13 3h-2v10h2V3zm4.83 2.17l-1.42 1.42C17.99 7.86 19 9.81 19 12c0 3.87-3.13 7-7 7s-7-3.13-7-7c0-2.19 1.01-4.14 2.58-5.42L6.17 5.17C4.23 6.82 3 9.26 3 12c0 4.97 4.03 9 9 9s9-4.03 9-9c0-2.74-1.23-5.18-3.17-6.83z"></path></svg>
                    <span>Dashboard</span>
                </a></li>
                <?php if ($role == 'admin'): ?>
                    <li><a href="?page=appointments" class="<?php echo $page == 'appointments' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z"></path></svg>
                        <span>Appointments</span>
                    </a></li>
                    <li><a href="?page=veterinarians" class="<?php echo $page == 'veterinarians' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24"><path d="M16 17.01V10h-2v7.01h-3L15 21l4-3.99h-3zM9 3L5 6.99h3V14h2V6.99h3L9 3z"></path></svg>
                        <span>Veterinarians</span>
                    </a></li>
                    <li><a href="?page=pets" class="<?php echo $page == 'pets' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
                        <span>Pets</span>
                    </a></li>
                    <li><a href="?page=clients" class="<?php echo ($page == 'clients' || $page == 'view_client') ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24"><path d="M12 5.9c1.16 0 2.1.94 2.1 2.1s-.94 2.1-2.1 2.1S9.9 9.16 9.9 8s.94-2.1 2.1-2.1m0 9c2.97 0 6.1 1.46 6.1 2.1v1.1H5.9V17c0-.64 3.13-2.1 6.1-2.1M12 4C9.79 4 8 5.79 8 8s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 9c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4z"></path></svg>
                        <span>Clients</span>
                    </a></li>
                <?php else: // Client ?>
                    <li><a href="?page=my_appointments" class="<?php echo $page == 'my_appointments' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z"></path></svg>
                        <span>My Appointments</span>
                    </a></li>
                    <li><a href="?page=my_pets" class="<?php echo $page == 'my_pets' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
                        <span>My Pets</span>
                    </a></li>
                    <li><a href="?page=book_appointment" class="<?php echo $page == 'book_appointment' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"></path></svg>
                        <span>Book Appointment</span>
                    </a></li>
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
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.querySelector('input[name="appointment_date"]');
            const timeSelect = document.querySelector('select[name="appointment_time"]');

            if (dateInput) {
                // Set min/max dates
                const now = new Date();
                const today = now.toISOString().split('T')[0];
                now.setMonth(now.getMonth() + 2);
                const maxDate = now.toISOString().split('T')[0];
                dateInput.setAttribute('min', today);
                dateInput.setAttribute('max', maxDate);

                const updateAvailableTimes = (date) => {
                    if (!date || !timeSelect) return;

                    // Show loading state
                    timeSelect.innerHTML = '<option value="">Loading...</option>';
                    timeSelect.disabled = true;

                    fetch(`get_available_slots.php?date=${date}`)
                        .then(response => response.json())
                        .then(slots => {
                            timeSelect.innerHTML = ''; // Clear options
                            if (slots.length > 0) {
                                timeSelect.appendChild(new Option('-- Select a time --', ''));
                                slots.forEach(slot => {
                                    timeSelect.appendChild(new Option(slot.label, slot.value));
                                });
                            } else {
                                timeSelect.innerHTML = '<option value="">No available slots</option>';
                            }
                            timeSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error fetching available slots:', error);
                            timeSelect.innerHTML = '<option value="">Error loading times</option>';
                        });
                };

                dateInput.addEventListener('input', function(e) {
                    const selectedDate = new Date(e.target.value);
                    if (selectedDate.getUTCDay() === 0) {
                        alert('Clinic is closed on Sundays. Please select a different day.');
                        e.target.value = '';
                        timeSelect.innerHTML = '<option value="">-- Select a time --</option>';
                        return;
                    }
                    updateAvailableTimes(e.target.value);
                });

                // If a date is already selected on page load (e.g., due to form error + reload),
                // trigger the update.
                if (dateInput.value) {
                    updateAvailableTimes(dateInput.value);
                }
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const mainLayout = document.querySelector('.main-layout');

            sidebarToggle.addEventListener('click', function() {
                mainLayout.classList.toggle('sidebar-collapsed');
            });
        });
    </script>
</body>
</html>