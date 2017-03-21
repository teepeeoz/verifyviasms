## Welcome to Verify via SMS

This is a simple application to send a random verification code to a mobile phone, which can be read back to verify that the person has possesson of the mobile phone.

### Functionality

This application does two things:
1. Send a random verification code via SMS to the provided moble telephone number
2. Verifies the input verification code against the code sent to the mobile

## User story #1

As a customer service representative (CSR) in a contact center, I wish to verify the incoming caller quickly using their mobile phone.

A customer has supplied their details to the organisation including their mobile phone number and these details are commonly used to verify the customer on incoming calls. The verification process takes valuable time that could be used instead to address the customers needs.  The verification process can have its own issues in that some details held on the system may not match the customers current detail such as their work direct telephone number or the details are on the drivers license - and the wallet has been lost and is now in someone elses possession.  
 
The SMS verification process is used by many companies as part of their 2 factor authentication process for application access and this application manually extends the use case to the contact center.

This verification application process could work as follows:-
- Customer calls the contact centre
- CSR uses the customers name and Date of Birth (DOB) to locate the customer record.  The DOB is the first (weak) secret.
- The customer record also holds the mobile phone number
- The CSR copies and pastes the mobile phone number to the application and clicks send SMS
- The customer reads out the verificaton code on the SMS to the CSR
- The CSR enters the verification code into the application second secret
- If the code matches, then the 2 factor authentication is successful and the CSR can consider the caller to be identified

*Note*: For calls that involve changing sensitive data, additional verification steps should be considered later in the call.  The reasoning behind this is that a mobile phone and wallet may be lost at the same, such as when the mobile phone case contains space for cards and drivers license.

## User story #2

As a customer representative (CSR) in a contact centre, I wish to verify the validity of a prospects mobile number.

While we are familiar with emails from a new service that require you to click on a link on an email to verify your email address, it is less frequent that a contact centre will verify the mobile number you have just supplied.  This can have the drawback that the incorrect mobile number is supplied or captured due to keying errors.

The SMS verification process can be used to verify that a new prospect has the mobile and that the CSR has captured the correct mobile number for the prospect.  The same process could also be used to verify a changed mobile number for an exsiting customer.

*Note*: The mobile number should not be used for verification of the caller if the mobile number is being changed at the same time.  Additional security questions should be asked if the mobile number is changing.  The same heightened security process should be followed s when the customers address changes. 


## Configuration

The majority of the configuration settings are in the "settings.php" file.

The exception to the configuration are the TWILIO SID and token values.  These are set using environment variables named "TWILIO_SID" and "TWILIO_TOKEN".  The reason for this is to enable deployment from git without including credentials. 

If you wish to enable he application only under HTTPS, then a valid SSL certificate is required.  For a no cost option, consider using https://letsencrypt.org/.  For SSL only operation change the $USE_HTTPS value to true in settings.

The settings file contains some of the common messages, including error messages, so change theses as you deem appropriate.

## TWILIO

Twilio is a servcie provider for communication APIs.  In this case Twilio is used for sending a SMS verification code to the recipients mobile phone number.

If you wish to use this application you will need to donwload the Twilio SDK for PHP ( https://www.twilio.com/docs/libraries/php ) and get your own Twilio account.  Once you have a Twilio account change the setting variable $TWILIO_NUMBER value to YOUR TWILIO phone number and set the environment variables named "TWILIO_SID" and "TWILIO_TOKEN".  

## Usage Notes 

This sample application is:

1. Based on Twilio as the SMS service.  To use this application you need to get your own account
2. Just because the person at the other end can supply the correct verificaton code does not imply that the person is the legitimate mobile number owner.  Mobile numbers can be ported or SMS are visible via authorised desktop applications.
3. The mobile number is configured for validating againts Australian mobile numbers only.  Change the regex in the Javascript for your country and mobile number format to validate or alternatively remove validation
4. The default setup of this web page asks for a passphrase to ensure only authorised users sends messages as there are costs associated with SMS sending
5. If you have alternate authentication methods and are behind a firewall, you may consider removign the passphrase for easy of use
6. If the daily SMS send limit is reached on this demo, then the application switches to simulation mode
	
