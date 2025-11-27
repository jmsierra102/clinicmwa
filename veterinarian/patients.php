<?php
// veterinarian/patients.php

$vet_id = $_SESSION['id'];
?>

<h2>My Patients</h2>
<p>This list shows all pets you have had an appointment with.</p>
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
            $sql = "SELECT DISTINCT p.id, p.name, p.species, p.breed, p.age, u.firstname, u.lastname
                    FROM pets p
                    JOIN users u ON p.owner_id = u.id
                    JOIN appointments a ON a.pet_id = p.id
                    WHERE a.vet_id = ?
                    ORDER BY p.name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $vet_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . e($row['name']) . "</td>";
                    echo "<td>" . e($row['species']) . "</td>";
                    echo "<td>" . e($row['breed']) . "</td>";
                    echo "<td>" . e($row['age']) . "</td>";
                    echo "<td>" . e($row['firstname'] . ' ' . $row['lastname']) . "</td>";
                    echo '<td class="actions"><a href="?page=medical_records&pet_id=' . e($row['id']) . '" class="btn btn-sm btn-primary">View Records</a></td>';
                    echo "</tr>";
                }
            } else {
                echo '<tr><td colspan="6">You have no patients yet.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
