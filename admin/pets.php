<?php
// admin/pets.php
?>

<h2>All Registered Pets</h2>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Species</th>
                <th>Breed</th>
                <th>Age</th>
                <th>Owner</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT p.id, p.name, p.species, p.breed, p.age, u.firstname, u.lastname, u.email
                    FROM pets p
                    JOIN users u ON p.owner_id = u.id
                    ORDER BY u.lastname, p.name ASC";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . e($row['name']) . "</td>";
                    echo "<td>" . e($row['species']) . "</td>";
                    echo "<td>" . e($row['breed']) . "</td>";
                    echo "<td>" . e($row['age']) . "</td>";
                    echo "<td>" . e($row['firstname'] . ' ' . $row['lastname']) . " (" . e($row['email']) . ")</td>";
                    echo '<td><a href="?page=medical_records&pet_id=' . e($row['id']) . '">Medical Records</a></td>';
                    echo "</tr>";
                }
            } else {
                echo '<tr><td colspan="6">No pets found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
