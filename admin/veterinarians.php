<?php
// admin/veterinarians.php

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';

// Handle POST requests for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    if ($action == 'add') {
        $new_id = generate_id('VT', $conn, 'users');
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'veterinarian';
        
        $sql = "INSERT INTO users (id, firstname, lastname, email, password, role, phone) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $new_id, $firstname, $lastname, $email, $hashed_password, $role, $phone);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Veterinarian added successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error adding veterinarian.</div>';
        }
        $action = 'list'; // Return to list view
    } elseif ($action == 'edit' && $id) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET firstname = ?, lastname = ?, email = ?, phone = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $firstname, $lastname, $email, $phone, $hashed_password, $id);
        } else {
            $sql = "UPDATE users SET firstname = ?, lastname = ?, email = ?, phone = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $firstname, $lastname, $email, $phone, $id);
        }

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Veterinarian updated successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating veterinarian.</div>';
        }
        $action = 'list'; // Return to list view
    }
}

// Handle delete request
if ($action == 'delete' && $id) {
    $sql = "DELETE FROM users WHERE id = ? AND role = 'veterinarian'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Veterinarian deleted successfully.</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting veterinarian.</div>';
    }
    $action = 'list'; // Return to list view
}

echo $message;

if ($action == 'add' || $action == 'edit') {
    $current_data = ['firstname' => '', 'lastname' => '', 'email' => '', 'phone' => ''];
    if ($action == 'edit' && $id) {
        $stmt = $conn->prepare("SELECT firstname, lastname, email, phone FROM users WHERE id = ? AND role = 'veterinarian'");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $current_data = $result->fetch_assoc();
        }
    }
?>
    <h2><?php echo ucfirst($action); ?> Veterinarian</h2>
    <div class="form-wrapper">
        <form action="?page=veterinarians&action=<?php echo $action; ?><?php echo $id ? '&id='.$id : ''; ?>" method="post">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="firstname" value="<?php echo e($current_data['firstname']); ?>" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="lastname" value="<?php echo e($current_data['lastname']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo e($current_data['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo e($current_data['phone']); ?>">
            </div>
            <div class="form-group">
                <label>Password <?php if($action == 'edit') echo '(leave blank to keep current password)'; ?></label>
                <input type="password" name="password" <?php if($action == 'add') echo 'required'; ?> >
            </div>
            <button type="submit" class="btn btn-primary">Save Veterinarian</button>
            <a href="?page=veterinarians" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php
} else {
?>
    <h2>Manage Veterinarians</h2>
    <a href="?page=veterinarians&action=add" class="btn btn-primary" style="margin-bottom: 20px;">Add New Veterinarian</a>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT id, firstname, lastname, email, phone FROM users WHERE role = 'veterinarian' ORDER BY lastname, firstname ASC");
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . get_vet_name(e($row['firstname']) . ' ' . e($row['lastname'])) . "</td>";
                        echo "<td>" . e($row['email']) . "</td>";
                        echo "<td>" . e($row['phone']) . "</td>";
                        echo '<td class="actions">';
                        echo '<a href="?page=veterinarians&action=edit&id=' . e($row['id']) . '">Edit</a>';
                        echo '<a href="?page=veterinarians&action=delete&id=' . e($row['id']) . '" onclick="return confirm(\'Are you sure?\');" class="delete">Delete</a>';
                        echo '</td>';
                        echo "</tr>";
                    }
                } else {
                    echo '<tr><td colspan="4">No veterinarians found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
}
?>