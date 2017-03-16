<?php
	/*
	SMS verification capability settings file
	UPDATE THE VALUES IN THIS FILE 	
	*/

	// Choose a passphrase and find the sha256 hash of that passphrase.
	// Use an online function to generate the hash: http://www.xorbin.com/tools/sha256-hash-calculator.
	$PASSPHRASE_HASH = "c2333a7e3a607935c67c1e6f6810395decc9f66f592b812aaada7db94ba215d6";

	$TWILIO_NUMBER = "+61428747269";
	
	// TWILIO SID and Token are fetched as ENVIRONMENT variables not 
	// as values here as per best practice for DevOps and security
	// e.g. https://12factor.net/config

	$VERCODE_MIN = 10000; // Lowest number
	$VERCODE_MAX = 99999; // Larget number
	$SMS_MESSAGE = "Your verification code is ";
	
	$COUNTER_FILE = "/opt/www.data/sms%s.counter";
	$COUNTER_MAX = 5;
	$ACCESSLOG_FILE = "/opt/www.data/sms%s.access";
	
	// Set the below to "true" to force the use of TSL
	$USE_HTTPS = false;
	
	$BOOTSTRAP_LOCATION_PREFIX = "";
	
	$MSG01 = "<p style='color:#CC0000;'>Incorrect verification code supplied.  Please retry or reset.</p>";
	$MSG02 = "<p>Click Next for next verification.</p>";
	$MSG03 = "<p><b>Enter verification code below.</b></p>";
	$MSG04 = "<p>To send a verification code, enter a mobile number and the password.</b></p>";
	
	$HDR01 = "Send verify code via SMS";
	$HDR02 = "Verify Code";
	$HDR03 = "<div style='color:#44FF44;'>Approved</div>";;
?>
