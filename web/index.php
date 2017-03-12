<?php 

require_once('settings.php');
require 'vendor/autoload.php';
 
if ($USE_HTTPS)
{
   // STS headers set for 90 days
   header("Strict-Transport-Security: max-age=7776000"); 
}
// Enable flushing
ini_set('implicit_flush', true);
ob_implicit_flush(true);
ob_end_flush();

//Set the correct protocol
if ($USE_HTTPS && !$_SERVER['HTTPS'])
{
   // Redirect
   header("Location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
   exit;
}

if ($COUNTER_FILE != "")
{
	$dateFormatted = date("Ymd");
	$COUNTER_FILE = sprintf($COUNTER_FILE, $dateFormatted);
	if (!file_exists($COUNTER_FILE)) {
		$fp = fopen($COUNTER_FILE, "w");
		fwrite($fp,"00000");
		fclose($fp);
	}
}
?>

<!DOCTYPE html>
<html lang="en" >
  <head>
    <title>Verify via SMS</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A utility to verify perosn via SMS">
    <meta name="author" content="Tom Peltonen">

    <!-- Le styles -->
    <link href="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 40px !important;
        padding-bottom: 40px;
        background-color: #f5f5f5;
      }

      .form-signin {
        max-width: 600px;
        padding: 19px 29px 29px;
        margin: 0 auto 20px;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
      .form-signin .form-signin-heading,
      .form-signin .checkbox {
        margin-bottom: 10px;
      }
      .form-signin input[type="text"],
      .form-signin input[type="password"] {
        font-size: 16px;
        height: auto;
        margin-bottom: 15px;
        padding: 7px 9px;
      }

    </style>
    <link href="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/ico/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/ico/favicon.png">
  </head>

  <body>

    <div class="container">
		
		 <?php
		// Check if we are setup for SMS
		$fail = false;
		if (getenv("TWILIO_SID") == "")
		{
			echo "<p>TWILIO_SID is not set.</p>";
			$fail = true;
		}
		if (getenv("TWILIO_TOKEN") == "")
		{
			echo "<p>TWILIO_TOKEN is not set.</p>";
			$fail = true;
		}
		if ($TWILIO_NUMBER == "")
		{
			echo "<p>TWILIO_NUMBER is not set.</p>";
			$fail = true;
		}
		if ($COUNTER_FILE == "")
		{
			echo "<p>COUNTER_FILE is not set.</p>";
			$fail = true;
		}
		if ($SMS_MESSAGE == "")
		{
			echo "<p>SMS_MESSAGE is not set.</p>";
			$fail = true;
		}

	
			
		if ($fail) 
		{
			echo "<p>Due to setup errors you cannot proceed.</p>";
		} else
		{
		 ?>

    	<form class="form-signin" method="post" name="authForm" >
        	<h3 class="form-signin-heading">
			<?php
			
				$mobile = "";
				if ( isset($_POST['mobilenumber']) )
					$mobile = $_POST['mobilenumber'];
					
				$error_msg = "";
				$action_send = false;
				$action_verify = false;
				$action_reset = false;
				$fail = false;
				
				if ( isset($_POST['btnSend']) )
				{
					$action_send = true;
					$fail = true;
					if (isset($_POST['password']) )
					{
						$hash = hash("sha256", $_POST['password']);
						if ($hash == $PASSPHRASE_HASH)
							$fail = false;
						else
							$error_msg = "Invalid Passphrase. Request Denied.";
					} 
					else
						$error_msg = "Invalid Passphrase. Request Denied.";

					if (!$fail)
					{

						$fp = fopen($COUNTER_FILE, "r");
						$count = (int)fread($fp, filesize($COUNTER_FILE));
						fclose($fp);
						if ($count > $COUNTER_MAX)
						{
							//$fail = true; // if fail not set then simulation occurs
							$error_msg = "Maximum SMS sends reached";
						}

					}
				}
				if ( isset($_POST['btnVerify']) )
				{
					$action_verify = true;
					$fail = true;
					if ( isset($_POST['verifycode']) )
					{
						$hash = hash("sha256", $_POST['verifycode']);
						if ($hash == $_POST['verifymastercode'])
							$fail = false;
					}
				}
				if ( isset($_POST['btnReset']) )
				{
					$mobile = "";
					$action_reset = true;
				}				

				if ($action_send && !$fail) {
					echo "Verify Code</h3>"; 
                	echo "<p>Approved. Sending SMS code...</p>";

					$vercode = sprintf("%05d", mt_rand($VERCODE_MIN, $VERCODE_MAX));
					$smsmessage = $SMS_MESSAGE . $vercode;
 
					$fp = fopen($COUNTER_FILE, "r");
					$count = (int)fread($fp, filesize($COUNTER_FILE));
					fclose($fp);
					// If daily maximum quota exceeded then simulate
					if ($count >= $COUNTER_MAX)
					{
						echo "<p>Maximum SMS sends reached. Simulating send.</p>";
						$smsmessage = $SMS_MESSAGE . "XXXXX (" . $vercode . ")";
						echo "<p>To Mobile number: " . $mobile . "</p>";
						echo "<p>Sent message: &quot;" . $smsmessage . "&quot; ID: " . "</p>";
					}
					else
			        {
					
						$AccountSid = getenv("TWILIO_SID"); 
						$AuthToken = getenv("TWILIO_TOKEN");
						$twilioclient = new Twilio\Rest\Client($AccountSid, $AuthToken);

						$sms = $twilioclient->messages->create( $mobile,
							array(
							'from' => $TWILIO_NUMBER, 
							'body' => $smsmessage)
						);
					
						$smsmessage = $SMS_MESSAGE . "XXXXX ";
						echo "<p>To Mobile number: " . $mobile . "</p>";
						echo "<p>Sent message: " . $smsmessage . " ID: " . $sms->sid . "</p>";
					}

						$fp = fopen($COUNTER_FILE, 'c+');
						flock($fp, LOCK_EX);

						$count = (int)fread($fp, filesize($COUNTER_FILE));
						ftruncate($fp, 0);
						fseek($fp, 0);
						fwrite($fp, sprintf("%05d", $count + 1));

						flock($fp, LOCK_UN);
						fclose($fp);
					
					echo "<p>From Telephone number: " . $TWILIO_NUMBER;
					echo $MSG03;
				?>
					<input type="hidden" name="mobilenumber" value="<?php echo $mobile; ?>">
					<input type="hidden" name="verifymastercode" value="<?php echo hash("sha256", $vercode); ?>">
					<input type="password" autocomplete=off class="input-block-level" placeholder="Enter code" name="verifycode" maxlength="10">
	                <input class="btn btn-large btn-primary" type="submit" name="btnVerify" value="Verify Code"/>
				<?php 
					
				} elseif ($action_verify) 
				{
					if ($fail)
					{
						echo "Verify Code</h3>"; 
						echo $MSG01;
					?>					
						<input type="hidden" name="mobilenumber" value="<?php echo $mobile; ?>">
						<input type="hidden" name="verifymastercode" value="<?php echo $_POST['verifymastercode']; ?>">
						<input type="password" autocomplete=off class="input-block-level" placeholder="Enter code" name="verifycode" maxlength="10">
						<input class="btn btn-large btn-primary" type="submit" name="btnVerify" value="Verify Code"/>
					<?php 
					} 
					else 
						echo "Approved</h3>"; 
						echo $MSG02;
				?>
					<input class="btn btn-large btn-primary" type="submit" name="btnNext" value="Next"/>
					<?php 
				} else 
				{
					echo "Send verify code via SMS</h3>";
					if ($action_send && $fail) {
						echo "<p style='color:#CC0000;'><b>" . $error_msg . "</b></p>";
					} elseif ($action_reset)
					{
						echo "<p style='color:#111111;'><b>Reset requested.</b></p>";
					} 
					echo $MSG04;
				?>
					<input type="tel" autocomplete=off class="input-block-level" placeholder="Enter Mobile Number" name="mobilenumber" value="<?php echo $mobile; ?>" maxlength="14">
					<input type="password" autocomplete=off class="input-block-level" placeholder="Enter Passphrase" name="password">
					<input class="btn btn-large btn-primary" type="submit" name="btnSend" value="Send SMS"/>
				<?php 
				}
 			?>
			<input class="btn btn-large" type="submit" name="btnReset" value="Reset"/>
		</form>
		<p id="feedbackMessage" name="feedbackMessage"></p>
		<?php 
		// End of setup is done
		}
		?>
    </div> <!-- /container -->
    <script src="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		
		function validate()
		{
			if (!phonenumber(document.authForm.mobilenumber))
				return false;
				
			return true;
		}
	
	    function phonenumber(inputtxt)  
		{  
			var phonenoPattern = /^\({0,1}((0|\+61)(2|4|3|7|8)){0,1}\){0,1}(\ |-){0,1}[0-9]{2}(\ |-){0,1}[0-9]{2}(\ |-){0,1}[0-9]{1}(\ |-){0,1}[0-9]{3}$/g; 
			if(inputtxt.value.match(phonenoPattern))  
			{  
				return true;        
			}  
			else  
			{  
			    alert("Please Enter your Mobile Number");
				document.feedbackMessage.value = "Invalid mobile number";
				return false;  
			}  
		}  
	</script>
  </body>
</html>
