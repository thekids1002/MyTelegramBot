<?php
define('HOST', 'localhost');
define('USERNAME', 'sksuwttd_mywebsite');
define('PASSWORD', 'npnn2001');
define('DATABASE', 'sksuwttd_mywebsite');
$conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);

// Check connection
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	exit();
}

//base các hàm thao tác với csdl thường dùng.
function execute($sql)
{
	$con = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
	mysqli_query($con, $sql);
if(mysqli_affected_rows($con) > 0){ return true;} else { return false; }
	if ($con->error) {
		die("Connection failed: " . mysqli_error($con));
		mysqli_close($con);
		return false;
	}
	mysqli_close($con);
	return true;
}

function execute2($sql)
{
	$con = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);

	if (mysqli_query($con, $sql)) {
		$last_id = mysqli_insert_id($con);
	}

	if ($con->error) {
		die("Connection failed: " . mysqli_error($con));
	}

	mysqli_close($con);
	return $last_id;
}

function executeResult($sql)
{
	$con = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
	
	$result = mysqli_query($con, $sql);
	$data   = [];
	if ($con->error) {
		die("Connection failed: " . mysqli_error($con));
	}
	if ($result != null) {
		while ($row = mysqli_fetch_array($result, 1)) {
			$data[] = $row;
		}
	}

	mysqli_close($con);

	return $data;
}

function executeSingleResult($sql)
{

	$con = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);

	$result = mysqli_query($con, $sql);
	$row    = null;
	if ($result != null) {
		$row = mysqli_fetch_array($result, 1);
	}

	mysqli_close($con);

	return $row;
}