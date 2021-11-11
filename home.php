<?php

//home.php

session_start();

if(!isset($_SESSION["user_id"]))
{
	header("location:login.php");
}

include('database_connection.php');

include('function.php');

$user_name = '';
$user_id = '';

if(isset($_SESSION["user_name"], $_SESSION["user_id"]))
{
	$user_name = $_SESSION["user_name"];
	$user_id = $_SESSION["user_id"];
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>PHP Login with OTP Authentication</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script src="http://code.jquery.com/jquery.js"></script>
    	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	</head>
	<body>
		<br />
		<div class="container">
			<h3 align="center">How to Make Initial Avatar Image in PHP After Registration</h3>
			<br />
			<br />
			<div class="row">
				<div class="col-md-9">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">User Timeline</h3>
						</div>
						<div class="panel-body">
							<h1 align="center">Welcome <?php echo $user_name; ?></h1>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">User Details</h3>
						</div>
						<div class="panel-body">
							<div align="center">
								<?php
								Get_user_avatar($user_id, $connect);
								echo '<br /><br />';
								echo $user_name;
								?>
								<br />
								<br />
								<a href="logout.php" class="btn btn-default">Logout</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<br />
		<br />
	</body>
</html>