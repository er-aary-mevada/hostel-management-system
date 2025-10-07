 <?php
  define('DB_SERVER', '127.0.0.1');
     define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'hostel_db');

 // Enable error reporting for mysqli
 mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

 try {
     $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
     $conn->set_charset("utf8");
 } catch (mysqli_sql_exception $e) {
     error_log("Database connection failed: " . $e->getMessage());
     die("ERROR: Could not connect to database. Please try again later.");
 }

 // Function to check if column exists
 function columnExists($conn, $table, $column) {
     try {
         $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
         return $result->num_rows > 0;
     } catch (Exception $e) {
         error_log("Column check error: " . $e->getMessage());
         return false;
     }
 }

 // Function to safely get column value
 function safeGetColumn($row, $column, $default = '') {
     return isset($row[$column]) ? $row[$column] : $default;
 }
?>