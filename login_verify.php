<?php

//login_verify.php



$connect = new PDO("mysql:host=localhost;dbname=testing", "root", "");

session_start();

$error = '';

$next_action = '';

sleep(2);

if(isset($_POST["action"]))
{
	if($_POST["action"] == 'email')
	{
		if($_POST["user_email"] != '')
		{
			$data = array(
				':user_email'	=>	$_POST["user_email"]
			);

			$query = "
			SELECT * FROM register_user 
			WHERE user_email = :user_email
			";

			$statement = $connect->prepare($query);

			$statement->execute($data);

			$total_row = $statement->rowCount();

			if($total_row == 0)
			{
				$error = 'Email Address not found';

				$next_action = 'email';
			}
			else
			{
				$result = $statement->fetchAll();

				foreach($result as $row)
				{
					$_SESSION["register_user_id"] = $row["register_user_id"];

					$_SESSION["user_name"] = $row["user_name"];

					$_SESSION['user_email'] = $row["user_email"];

					$_SESSION["user_password"] = $row["user_password"];
				}
				$next_action = 'password';
			}
		}
		else
		{
			$error = 'Email Address is Required';

			$next_action = 'email';
		}
	}

	if($_POST["action"] == 'password')
	{
		if($_POST["user_password"] != '')
		{
			if(password_verify($_POST["user_password"], $_SESSION["user_password"]))
			{
				$login_otp = rand(100000,999999);

				$data = array(
					':user_id'		=>	$_SESSION["register_user_id"],
					':login_otp'	=>	$login_otp,
					':last_activity'=>	date('d-m-y h:i:s')
				);

				$query = "
				INSERT INTO login_data 
				(user_id, login_otp, last_activity) 
				VALUES (:user_id, :login_otp, :last_activity)
				";

				$statement = $connect->prepare($query);

				if($statement->execute($data))
				{
					$_SESSION['login_id'] = $connect->lastInsertId();
					$_SESSION['login_otp'] = $login_otp;

					require 'class/class.phpmailer.php';

					$mail = new PHPMailer;

					$mail->IsSMTP();

					$mail->Host = 'smtpout.secureserver.net';

					$mail->Port = '80';

					$mail->SMTPAuth = true;

					$mail->Username = 'xxxxxxxx';
					
					$mail->Password = 'xxxxxxxx';

					$mail->SMTPSecure = '';

					$mail->From = 'tutorial@webslesson.info';

					$mail->FromName = 'Webslesson';

					$mail->AddAddress($_SESSION["user_email"]);

					$mail->WordWrap = 50;

					$mail->IsHTML(true);

					$mail->Subject = 'Verification code for Login';

					$message_body = '
					<p>For verify your login details, enter this verification code when prompted: <b>'.$login_otp.'</b>.</p>
					<p>Sincerely,</p>
					';

					$mail->Body = $message_body;

					if($mail->Send())
					{
						$next_action = 'otp';
					}
					else
					{
						$error = '<label class="text-danger">'.$mail->ErrorInfo.'</label>';
						$next_action = 'password';
					}
				}
			}
			else
			{
				$error = 'Wrong Password';
				$next_action = 'password';
			}
		}
		else
		{
			$error = 'Password is Required';
			$next_action = 'password';
		}
	}

	if($_POST["action"] == "otp")
	{
		if($_POST["user_otp"] != '')
		{
			if($_SESSION['login_otp'] == $_POST["user_otp"])
			{
				$_SESSION['user_id'] = $_SESSION['register_user_id'];
				unset($_SESSION["register_user_id"]);
				unset($_SESSION["user_email"]);
				unset($_SESSION["user_password"]);
				unset($_SESSION["login_otp"]);
			}
			else
			{
				$error = 'Wrong OTP Number';
				$next_action = 'otp';
			}
		}
		else
		{
			$error = 'OTP Number is required';
			$next_action = 'otp';
		}
	}





	$output = array(
		'error'			=>	$error,
		'next_action'	=>	$next_action
	);

	echo json_encode($output);
}


?>