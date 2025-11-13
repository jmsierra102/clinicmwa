<?php

/**
 * Generates a unique 6-digit alphanumeric ID with a prefix.
 *
 * @param string $prefix Two-letter prefix (e.g., 'AP' for appointment).
 * @param mysqli $conn Database connection object.
 * @param string $table The table to check for ID uniqueness.
 * @param string $column The column in the table to check against.
 * @return string The unique 6-digit ID.
 */
function generate_id($prefix, $conn, $table, $column = 'id') {
    do {
        $number = mt_rand(1000, 9999);
        $id = $prefix . '-' . $number;
        $sql = "SELECT $column FROM $table WHERE $column = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    
    $stmt->close();
    return $id;
}

/**
 * Sanitizes output to prevent XSS.
 *
 * @param string|null $value The string to sanitize.
 * @return string The sanitized string.
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Gets the full name of a veterinarian with the "Dr." prefix.
 *
 * @param string $vet_name The veterinarian's name.
 * @return string The prefixed name.
 */
function get_vet_name($vet_name) {
    return 'Dr. ' . e($vet_name);
}

?>