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

if ($ACCESSLOG_FILE != "")
{
	$dateFormatted = date("Ymd");
	$ACCESSLOG_FILE = sprintf($ACCESSLOG_FILE, $dateFormatted);
	if (!file_exists($ACCESSLOG_FILE)) {
		$fp = fopen($ACCESSLOG_FILE, "w");
		fwrite($fp,"\n");
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
		 
	// Get Remote IP for recording		 
	function get_ip() {
		//Just get the headers if we can or else use the SERVER global
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers = apache_request_headers();
		} else {
			$headers = $_SERVER;
		}
		//Get the forwarded IP if it exists
		if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$the_ip = $headers['X-Forwarded-For'];
		} elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )
		) {
			$the_ip = $headers['HTTP_X_FORWARDED_FOR'];
		} else {
			
			$the_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
		}
		return $the_ip;
	}
		 
		 
		// Log user details
		$fp = fopen($ACCESSLOG_FILE, 'a');
		flock($fp, LOCK_EX);
		$dateFormatted = date("c");
		fwrite($fp, $dateFormatted);
		fwrite($fp, "\t");
		fwrite($fp, get_ip());
		fwrite($fp, "\t");
		fwrite($fp, $_SERVER['HTTP_USER_AGENT']);
		fwrite($fp, "\n");
		flock($fp, LOCK_UN);
		fclose($fp);
		 
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

    	<form class="form-signin" method="post" name="authForm"  onsubmit="return ValidationEvent()">
        	<h3 class="form-signin-heading">
			<?php
			
				$mobile = "";
				if ( isset($_POST['mobilenumber']) )
					$mobile = $_POST['mobilenumber'];
			
				$passphrase = "";
				if ( isset($_POST['password']) )
					$passphrase = $_POST['password'];
					
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
					echo $HDR02 . "</h3>"; 
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
					<input type="hidden" name="password" value="<?php echo $passphrase; ?>">
					<input type="password" autocomplete=off class="input-block-level" placeholder="Enter code" name="verifycode" maxlength="10">
	                <input class="btn btn-large btn-primary" type="submit" name="btnVerify" value="Verify Code"/>
				<?php 
					
				} elseif ($action_verify) 
				{
					if ($fail)
					{
						echo $HDR02 . "</h3>"; 
						echo $MSG01;
					?>					
						<input type="hidden" name="mobilenumber" value="<?php echo $mobile; ?>">
						<input type="hidden" name="verifymastercode" value="<?php echo $_POST['verifymastercode']; ?>">
						<input type="hidden" name="password" value="<?php echo $passphrase; ?>">
						<input type="password" autocomplete=off class="input-block-level" placeholder="Enter code" name="verifycode" maxlength="10">
						<input class="btn btn-large btn-primary" type="submit" name="btnVerify" value="Verify Code"/>
					<?php 
					} 
					else 
					{
						echo $HDR03 . "</h3>"; 
						echo $MSG02;
				?>
						<input type="hidden" name="password" value="<?php echo $passphrase; ?>">
						<input class="btn btn-large btn-primary" type="submit" name="btnNext" value="Next"/>
					<?php 
					}
				} else 
				{
					echo $HDR01 . "</h3>";
					if ($action_send && $fail) {
						echo "<p style='color:#CC0000;'><b>" . $error_msg . "</b></p>";
					} elseif ($action_reset)
					{
						echo "<p style='color:#111111;'><b>Reset requested.</b></p>";
					} 
					echo $MSG04;
				?>
					<input type="tel" autocomplete=off class="input-block-level" placeholder="Enter Mobile Number as +614..." name="mobilenumber" value="<?php echo $mobile; ?>" maxlength="14">
					<p style="color:#CC0000;" id="errorPhone"></p>
					<input type="password" autocomplete=off class="input-block-level" placeholder="Enter Passphrase" name="password" value="<?php echo $passphrase; ?>">
					<input class="btn btn-large btn-primary" type="submit" name="btnSend" value="Send SMS"  />
				<?php 
				}
 			?>
			<input class="btn btn-large" type="submit" name="btnReset" value="Reset" onClick="return resetForm()"/>
		</form>
		<hr />
		<h4>Notes</h4>
		<ol>
		<li>See <a href="https://github.com/teepeeoz/verifyviasms">https://github.com/teepeeoz/verifyviasms</a> for more details</li>
		<li>This application is configured for demonstration purposes</li>
		<li>Change the configuration to enable for your production environment</li>
		<li>If using this demo, please use only your own number for testing. Phone numbers are logged as is your browser fingerprint and IP address</li>
		</ol>
		<?php 
		// End of setup is done
		}
		?>
    </div> <!-- /container -->
    <script src="<?php echo $BOOTSTRAP_LOCATION_PREFIX; ?>bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		
		resetting = false;
		
		function resetForm()
		{
			resetting = true;
			return true;
		}
		
		function ValidationEvent()
		{
			document.getElementById("errorPhone").innerHTML = "";
			if (resetting)
			{
				resetting = false;
				return true;
			}
			
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
				document.getElementById("errorPhone").innerHTML = "<b>Invalid mobile number</b>";
				return false;  
			}  
		}  
	</script>
  </body>
</html>
