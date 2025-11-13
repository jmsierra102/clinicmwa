<?php
// admin/veterinarians.php

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';

// Handle POST requests for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    if ($action == 'add') {
        $new_id = generate_id('VT', $conn, 'veterinarians');
        $sql = "INSERT INTO veterinarians (id, name, email, phone) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $new_id, $name, $email, $phone);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Veterinarian added successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error adding veterinarian.</div>';
        }
        $action = 'list'; // Return to list view
    } elseif ($action == 'edit' && $id) {
        $sql = "UPDATE veterinarians SET name = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $phone, $id);
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
    $sql = "DELETE FROM veterinarians WHERE id = ?";
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
    $current_data = ['name' => '', 'email' => '', 'phone' => ''];
    if ($action == 'edit' && $id) {
        $stmt = $conn->prepare("SELECT * FROM veterinarians WHERE id = ?");
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
                <label>Full Name (e.g., John Doe)</label>
                <input type="text" name="name" value="<?php echo e($current_data['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo e($current_data['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo e($current_data['phone']); ?>">
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
                $result = $conn->query("SELECT * FROM veterinarians ORDER BY name ASC");
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . get_vet_name($row['name']) . "</td>";
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
