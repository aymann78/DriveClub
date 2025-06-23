<?php
/**
 * Database Connection
 * 
 * Establishes connection to the MySQL database
 */

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4
mysqli_set_charset($conn, "utf8mb4");

/**
 * Execute a query and return the result
 *
 * @param string $sql The SQL query to execute
 * @param array $params Parameters for prepared statement
 * @return mysqli_result|bool Result of the query
 */
function db_query($sql, $params = [])
{
    global $conn;
    
    if (empty($params)) {
        $result = mysqli_query($conn, $sql);
        // Si es un SELECT, devolver el result set; si no, devolver true/false
        if (stripos(trim($sql), 'SELECT') === 0) {
            return $result;
        }
        return $result ? true : false;
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt === false) {
        return false;
    }
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        // Detectar si es SELECT
        $is_select = stripos(trim($sql), 'SELECT') === 0;
        if ($is_select) {
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        return $result;
        } else {
            // UPDATE, DELETE, INSERT, etc.
            mysqli_stmt_close($stmt);
            return true;
        }
    }
    
    mysqli_stmt_close($stmt);
    return false;
}

/**
 * Fetch a single row as an associative array
 *
 * @param string $sql The SQL query
 * @param array $params Parameters for prepared statement
 * @return array|null The fetched row or null
 */
function db_fetch_assoc($sql, $params = [])
{
    $result = db_query($sql, $params);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Fetch all rows as an associative array
 *
 * @param string $sql The SQL query
 * @param array $params Parameters for prepared statement
 * @return array The fetched rows
 */
function db_fetch_all($sql, $params = [])
{
    $result = db_query($sql, $params);
    $rows = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    
    return $rows;
}

/**
 * Insert a row into a table
 *
 * @param string $table The table name
 * @param array $data Associative array of column => value
 * @return int|bool The inserted ID or false on failure
 */
function db_insert($table, $data)
{
    global $conn;
    
    $columns = array_keys($data);
    $values = array_values($data);
    
    $placeholders = array_fill(0, count($values), '?');
    
    $sql = "INSERT INTO `$table` (";
    $sql .= implode(", ", array_map(function($col) { return "`$col`"; }, $columns));
    $sql .= ") VALUES (";
    $sql .= implode(", ", $placeholders);
    $sql .= ")";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt === false) {
        return false;
    }
    
    $types = '';
    foreach ($values as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } elseif (is_string($value)) {
            $types .= 's';
        } else {
            $types .= 'b';
        }
    }
    
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    
    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return $insert_id;
    }
    
    mysqli_stmt_close($stmt);
    return false;
}

/**
 * Update rows in a table
 *
 * @param string $table The table name
 * @param array $data Associative array of column => value to update
 * @param string $where The WHERE clause
 * @param array $params Parameters for the WHERE clause
 * @return bool Success or failure
 */
function db_update($table, $data, $where, $params = [])
{
    global $conn;
    $set_clause = [];
    $values = [];
    foreach ($data as $column => $value) {
        $set_clause[] = "`$column` = ?";
        $values[] = $value;
    }
    $sql = "UPDATE `$table` SET " . implode(", ", $set_clause);
    if (!empty($where)) {
        $sql .= " WHERE $where";
    }
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        return mysqli_error($conn);
    }
    $all_params = array_merge($values, $params);
    $types = '';
    foreach ($all_params as $param) {
        if (is_int($param)) {
            $types .= 'i';
        } elseif (is_float($param)) {
            $types .= 'd';
        } elseif (is_string($param)) {
            $types .= 's';
        } else {
            $types .= 'b';
        }
    }
    mysqli_stmt_bind_param($stmt, $types, ...$all_params);
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected_rows > 0 ? true : 0;
    }
    $error = mysqli_error($conn);
    mysqli_stmt_close($stmt);
    return $error;
}

/**
 * Delete rows from a table
 *
 * @param string $table The table name
 * @param string $where The WHERE clause
 * @param array $params Parameters for the WHERE clause
 * @return bool Success or failure
 */
function db_delete($table, $where, $params = [])
{
    global $conn;
    
    $sql = "DELETE FROM `$table`";
    
    if (!empty($where)) {
        $sql .= " WHERE $where";
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt === false) {
        return false;
    }
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected_rows > 0;
    }
    
    mysqli_stmt_close($stmt);
    return false;
}

/**
 * Get the last inserted ID
 *
 * @return int The last inserted ID
 */
function db_last_insert_id()
{
    global $conn;
    return mysqli_insert_id($conn);
}

/**
 * Escape a string for use in a query
 *
 * @param string $string The string to escape
 * @return string The escaped string
 */
function db_escape($string)
{
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}
