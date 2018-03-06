<?php
include('connection.php');
if(isset($_GET['sysMsg'])){ #get votes of each field and amount of people
	$sql="SELECT field_votes FROM vote_result";
	$stmt=$conn->query($sql);
	$result=$stmt->fetchAll(PDO::FETCH_COLUMN,0);
	$total_votes=0; #amount of people
	foreach($result as $value){
		settype($value,'int');
		$total_votes+=$value;
	}
	echo json_encode($result);
}
if(isset($_GET['field'])){
	settype($_GET['field'],'string');
	$sql="UPDATE vote_result SET field_votes=field_votes+1 WHERE field_name='".$_GET['field']."'";	
	$stmt=$conn->query($sql);
	echo "updated.";
}
?>
