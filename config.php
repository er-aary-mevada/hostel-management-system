 <?php
  define('DB_SERVER', '127.0.0.1');
     define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'hostel_db');

 $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($conn === false){
       die("ERROR: Could not connect. " . $conn->connect_error);
}
?>