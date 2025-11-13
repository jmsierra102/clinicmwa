<?php
// client/my_pets.php

$user_id = $_SESSION['id'];
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';

// Ensure the pet belongs to the current user before any action
if ($id) {
    $check_sql = "SELECT id FROM pets WHERE id = ? AND owner_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $id, $user_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows == 0) {
        // Pet not found or doesn't belong to user, redirect or show error
        $message = '<div class="alert alert-danger">Invalid pet selected.</div>';
        $action = 'list';
        $id = null; // Prevent further actions on this ID
    }
}


// Handle POST requests for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $age = $_POST['age'];

    if ($action == 'add') {
        $new_id = generate_id('PT', $conn, 'pets');
        $sql = "INSERT INTO pets (id, name, species, breed, age, owner_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssis", $new_id, $name, $species, $breed, $age, $user_id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Pet added successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error adding pet.</div>';
        }
        $action = 'list';
    } elseif ($action == 'edit' && $id) {
        $sql = "UPDATE pets SET name = ?, species = ?, breed = ?, age = ? WHERE id = ? AND owner_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiss", $name, $species, $breed, $age, $id, $user_id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Pet updated successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating pet.</div>';
        }
        $action = 'list';
    }
}

// Handle delete request
if ($action == 'delete' && $id) {
    $sql = "DELETE FROM pets WHERE id = ? AND owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $id, $user_id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Pet deleted successfully.</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting pet. It may have appointments scheduled.</div>';
    }
    $action = 'list';
}

echo $message;

if ($action == 'add' || $action == 'edit') {
    $current_data = ['name' => '', 'species' => '', 'breed' => '', 'age' => ''];
    if ($action == 'edit' && $id) {
        $stmt = $conn->prepare("SELECT * FROM pets WHERE id = ? AND owner_id = ?");
        $stmt->bind_param("ss", $id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $current_data = $result->fetch_assoc();
        }
    }
?>
    <h2><?php echo ucfirst($action); ?> Pet</h2>
    <div class="form-wrapper">
        <form action="?page=my_pets&action=<?php echo $action; ?><?php echo $id ? '&id='.$id : ''; ?>" method="post">
            <div class="form-group">
                <label>Pet\'s Name</label>
                <input type="text" name="name" value="<?php echo e($current_data['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Species (e.g., Dog, Cat)</label>
                <input type="text" name="species" value="<?php echo e($current_data['species']); ?>">
            </div>
            <div class="form-group">
                <label>Breed</label>
                <input type="text" name="breed" value="<?php echo e($current_data['breed']); ?>">
            </div>
            <div class="form-group">
                <label>Age</label>
                <input type="number" name="age" value="<?php echo e($current_data['age']); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save Pet</button>
            <a href="?page=my_pets" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php
} else {
?>
    <h2>My Pets</h2>
    <a href="?page=my_pets&action=add" class="btn btn-primary" style="margin-bottom: 20px;">Add New Pet</a>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Species</th>
                    <th>Breed</th>
                    <th>Age</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM pets WHERE owner_id = ? ORDER BY name ASC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . e($row['name']) . "</td>";
                        echo "<td>" . e($row['species']) . "</td>";
                        echo "<td>" . e($row['breed']) . "</td>";
                        echo "<td>" . e($row['age']) . "</td>";
                        echo '<td class="actions">';
                        echo '<a href="?page=my_pets&action=edit&id=' . e($row['id']) . '">Edit </a>';
                        echo '<a href="?page=my_pets&action=delete&id=' . e($row['id']) . '" onclick="return confirm(\'Are you sure?\');" class="delete">Delete</a>';
                        echo '</td>';
                        echo "</tr>";
                    }
                } else {
                    echo '<tr><td colspan="5">You have not added any pets yet.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
}
?>
