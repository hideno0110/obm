<?php


//parse vcap
if( getenv("VCAP_SERVICES") ) {
    $json = getenv("VCAP_SERVICES");
} 
# No DB credentials
else {
    echo "No vcap services available.";
    return;
}

# Decode JSON and gather DB Info
$services_json = json_decode($json,true);
$blu = $services_json["sqldb"];
if (empty($blu)) {
    echo "No dashDB service instance is bound. Please bind a SQLDB service instance";
    return;
}

$bludb_config = $services_json["sqldb"][0]["credentials"];

// create DB connect string
$conn_string = "DRIVER={IBM DB2 ODBC DRIVER};DATABASE=".
   $bludb_config["db"].
   ";HOSTNAME=".
   $bludb_config["host"].
   ";PORT=".
   $bludb_config["port"].
   ";PROTOCOL=TCPIP;UID=".
   $bludb_config["username"].
   ";PWD=".
   $bludb_config["password"].
   ";";
  
  
// connect to BLUDB
$conn = db2_connect($conn_string, '', '');


if (!$conn) {
    die("SQSLSTATE value: " . db2_conn_error());
}

// sql to create table
$sql = "DROP TABLE PRINT";
$result = db2_exec($conn, $sql);

$sql = "CREATE TABLE PRINT (ID BIGINT NOT NULL, DESCRIPTION VARCHAR(255), IMGSRC VARCHAR(255), PRICE INTEGER, QUAN INTEGER, TITLE VARCHAR(255), PRIMARY KEY (ID))";

$result = db2_exec($conn, $sql);

if ($result) {
    echo "Table PRINT created successfully";
} else {
    echo "Error creating table!";
}


// Populate the test table
$prints = array(
    array(1, "Lauren's husband took this spectacular photo when they visited Antarctica in December of 2012. This is one of our hot sellers, so it rarely goes on sale.",
     'penguin.jpg', 100, 6, 'Antarctica'),
    

    array(2, "Lauren loves this photo even though she wasn't present when the photo was taken. Her husband took this photo on a guy's weekend in Alaska.",
     'alaska.jpg', 75, 1, 'Alaska'),
    array(3, "This photo is another one of Lauren's favorites! Her husband took these photos of the Sydney Opera House in 2011.",
     'sydney.jpg', 100, 0, 'Australia')
    
);

foreach ($prints as $print) {
    // single quotes MUST be doubled up for DB2
	$desc = str_replace("'","''",$print[1]);
	// print "INSERT INTO print (id, description, imgsrc, price, quan, title) VALUES ({$print[0]}, '{$desc}', '{$print[2]}', {$print[3]}, {$print[4]}, '{$print[5]}')";

    $rc = db2_exec($conn, "INSERT INTO print (id,  description, imgsrc, price, quan, title) VALUES ( {$print[0]}, '${desc}' , '{$print[2]}', {$print[3]}, {$print[4]}, '{$print[5]}'   )");
    if ($rc) {
        print "Insert... succeded ";
    }
}

db2_close($conn);
?>
