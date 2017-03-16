## Welcome to Verify via SMS

This is a simple application to send a random verification code to a mobile phone, which can be read back to verify that the person has possesson of the mobile phone.

### Functionality

This application does two things:
1. Send a random verification code via SMS to the provided moble telephone number
2. Verifies the input verification code against the code sent to the mobile

## User story

 

## Configuraton

The majority of the configuration settings are in the "settings.php" file.

The exception to the configuration are the TWILIO SID and token values.  These are set using environment variables named "TWILIO_SID" and "TWILIO_TOKEN".  The reason for this is to enable deployment from git without including credentials. 

If you wish to enable only under HTTPS, then a valid SSL certificate is required.  For a no cost option, consider using https://letsencrypt.org/.  For SSL only operation change the $USE_HTTPS value to true in settings.

## TWILIO

Twilio is a servcie provider for communication APIs.  In this case TWILLIO is used for sending a SMS verification code to the recipients mobile phone number.

If you wish to use this application you will need to donwload the Twilio SDK for PHP ( https://www.twilio.com/docs/libraries/php ) and get your own Twilio account.  Once you have a Twilio account change the variable $TWILIO_NUMBER value to your phone number and set the environment variables named "TWILIO_SID" and "TWILIO_TOKEN".  

## Usage Notes 

This sample application is:

1. Based on Twilio as the SMS service.  To use this application you need to get your own account
2. Just because the person at the other end can supply the correct verificaton code does not imply that the person is the legitimate mobile number owner.  Mobile numbers can be ported or SMS are visible via authorised desktop applications.
3. The mobile number is configured for Australian mobile numbers only.  Change the regex in the Javascript for your country and mobile number format
4. The default setup of this web page asks for a passphrase to ensure only authorised users sends messages as there are costs associated with SMS sending
5. If you have alternate authentication methods and are behind a firewall, you may consider removign the passphrase for easy of use
6. If the daily SMS send limit is reached on this demo, then the application switches to simulation mode
	
