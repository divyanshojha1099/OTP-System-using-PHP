<?php

//forget_password.php

include('database_connection.php');

$message = '';

session_start();

if(isset($_SESSION["user_id"]))
{
	header("location:home.php");
}

if(isset($_POST["submit"]))
{
	if(empty($_POST["user_email"]))
	{
		$message = '<div class="alert alert-danger">Email Address is required</div>';
	}
	else
	{
		$data = array(
			':user_email'	=>	trim($_POST["user_email"])
		);

		$query = "
		SELECT * FROM register_user 
		WHERE user_email = :user_email
		";

		$statement = $connect->prepare($query);

		$statement->execute($data);

		if($statement->rowCount() > 0)
		{
			$result = $statement->fetchAll();

			foreach($result as $row)
			{
				if($row["user_email_status"] == 'not verified')
				{
					$message = '<div class="alert alert-info">Your Email Address is not verify, so first verify your email address by click on this <a href="resend_email_otp.php">link</a></div>';
				}
				else
				{
					$user_otp = rand(100000, 999999);

					$sub_query = "
					UPDATE register_user 
					SET user_otp = '".$user_otp."' 
					WHERE register_user_id = '".$row["register_user_id"]."'
					";

					$connect->query($sub_query);

					require 'class/class.phpmailer.php';

					$mail = new PHPMailer;

					$mail->IsSMTP();

					$mail->Host = 'smtpout.secureserver.net';

					$mail->Port = '80';

					$mail->SMTPAuth = true;

					$mail->Username = 'xxxxxxxxxx';

					$mail->Password = 'xxxxxxxxxx';

					$mail->SMTPSecure = '';

					$mail->From = 'tutorial@webslesson.info';

					$mail->FromName = 'Webslesson';

					$mail->AddAddress($row["user_email"]);

					$mail->IsHTML(true);

					$mail->Subject = 'Password reset request for your account';

					$message_body = '
					<p>For reset your password, you have to enter this verification code when prompted: <b>'.$user_otp.'</b>.</p>
					<p>Sincerely,</p>
					';

					$mail->Body = $message_body;

					if($mail->Send())
					{
						echo '<script>alert("Please Check Your Email for password reset code")</script>';

						echo '<script>window.location.replace("forget_password.php?step2=1&code=' . $row["user_activation_code"] . '")</script>';
					}
				}
			}
		}
		else
		{
			$message = '<div class="alert alert-danger">Email Address not found in our record</div>';
		}
	}
}

if(isset($_POST["check_otp"]))
{
	if(empty($_POST["user_otp"]))
	{
		$message = '<div class="alert alert-danger">Enter OTP Number</div>';
	}
	else
	{
		$data = array(
			':user_activation_code'		=>	$_POST["user_code"],
			':user_otp'					=>	$_POST["user_otp"]
		);

		$query = "
		SELECT * FROM register_user 
		WHERE user_activation_code = :user_activation_code 
		AND user_otp = :user_otp
		";

		$statement = $connect->prepare($query);

		$statement->execute($data);

		if($statement->rowCount() > 0)
		{
			echo '<script>window.location.replace("forget_password.php?step3=1&code=' . $_POST["user_code"] . '")</script>';
		}
		else
		{
			$message = '<div class="alert alert-danger">Wrong OTP Number</div>';
		}
	}
}

if(isset($_POST["change_password"]))
{
	$new_password = $_POST["user_password"];
	$confirm_password = $_POST["confirm_password"];

	if($new_password == $confirm_password)
	{
		$query = "
		UPDATE register_user 
		SET user_password = '".password_hash($new_password, PASSWORD_DEFAULT)."' 
		WHERE user_activation_code = '".$_POST["user_code"]."'
		";

		$connect->query($query);

		echo '<script>window.location.replace("login.php?reset_password=success")</script>';
	}
	else
	{
		$message = '<div class="alert alert-danger">Confirm Password is not match</div>';
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Forgot Password script in PHP using OTP</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script src="http://code.jquery.com/jquery.js"></script>
    	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	</head>
	<body>
		<br />
		<div class="container">
			<h3 align="center">Forgot Password script in PHP using OTP</h3>
			<br />
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Forgot Password script in PHP using OTP</h3>
				</div>
				<div class="panel-body">
					<?php

					echo $message;

					if(isset($_GET["step1"]))
					{
					?>
					<form method="post">
						<div class="form-group">
							<label>Enter Your Email</label>
							<input type="text" name="user_email" class="form-control" />
						</div>
						<div class="form-group">
							<input type="submit" name="submit" class="btn btn-success" value="Send" />
						</div>
					</form>
					<?php
					}
					if(isset($_GET["step2"], $_GET["code"]))
					{
					?>
					<form method="POST">
						<div class="form-group">
							<label>Enter OTP Number</label>
							<input type="text" name="user_otp" class="form-control" />
						</div>
						<div class="form-group">
							<input type="hidden" name="user_code" value="<?php echo $_GET["code"]; ?>" />
							<input type="submit" name="check_otp" class="btn btn-success" value="Send" />
						</div>
					</form>
					<?php
					}

					if(isset($_GET["step3"], $_GET["code"]))
					{
					?>
					<form method="post">
						<div class="form-group">
							<label>Enter New Password</label>
							<input type="password" name="user_password" class="form-control" />
						</div>
						<div class="form-group">
							<label>Enter Confirm Password</label>
							<input type="password" name="confirm_password" class="form-control" />
						</div>
						<div class="form-group">
							<input type="hidden" name="user_code" value="<?php echo $_GET["code"]; ?>" />
							<input type="submit" name="change_password" class="btn btn-success" value="Change" />
						</div>
					</form>
					<?php	
					}
					?>
				</div>
			</div>
		</div>
		<br />
		<br />
	</body>
</html>