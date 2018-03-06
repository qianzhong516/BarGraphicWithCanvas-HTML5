<?php
	$username='root';
	$password='';
	try{
		$conn=new PDO("mysql:host=localhost;dbname=bargraph",$username,$password);
		$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e){
		echo"Connection Error: ".$e->getMessage();
	}
	return $conn;
?>
