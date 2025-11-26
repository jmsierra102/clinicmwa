<?php
// client/medical_records.php

$user_id = $_SESSION['id'];

?>
<h2>Your Pets' Medical Records</h2>
<p>Select a pet to view their complete medical history.</p>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Species</th>
                <th>Breed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM pets WHERE owner_id = ? ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $user_id);
            $stmt->execute();
            $pets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            if (count($pets) > 0) {
                foreach ($pets as $pet) {
                    echo "<tr>";
                    echo "<td>" . e($pet['name']) . "</td>";
                    echo "<td>" . e($pet['species']) . "</td>";
                    echo "<td>" . e($pet['breed']) . "</td>";
                    echo '<td><a href="?page=view_pet_medical_records&pet_id=' . e($pet['id']) . '" class="btn btn-primary">View Records</a></td>';
                    echo "</tr>";
                }
            } else {
                echo '<tr><td colspan="4">You have not added any pets yet. <a href="?page=my_pets&action=add">Add one now</a>.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
