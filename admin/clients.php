<?php
// admin/clients.php
?>

<h2>All Clients</h2>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Date Registered</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT firstname, lastname, email, created_at
                    FROM users
                    WHERE role = 'client'
                    ORDER BY lastname, firstname ASC";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $dt = new DateTime($row['created_at']);
                    echo "<tr>";
                    echo "<td>" . e($row['firstname'] . ' ' . $row['lastname']) . "</td>";
                    echo "<td>" . e($row['email']) . "</td>";
                    echo "<td>" . $dt->format('M d, Y') . "</td>";
                    echo "</tr>";
                }
            } else {
                echo '<tr><td colspan="3">No clients found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
