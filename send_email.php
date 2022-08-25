<?php

/*

*/

require("sendgrid-php/sendgrid-php.php");

global $email_success;

$email_success = false;







function send_password_email($email_to,$new_password){
	
	$email_from = "insights@iconanalytics.com"; # send grid api requires this pre-verified e-mail address to send email
	$subject = "Your new PetCalculator.com login password";
    try{

        $email_api = "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";

        $email = new \SendGrid\Mail\Mail(); 
        $email->setFrom($email_from, "PetCalculator Admin");
        $email->setSubject($subject);
        $email->addTo($email_to, 'PetCalculator' ." ". 'User');
		$email->addBcc('charlescharleschuck@gmail.com');
        $email->addContent("text/plain", 'Password Reset');
        $email->addContent(
            "text/html", '<div style="margin-right: auto;
            text-align: center;
            vertical-align: middle;">
        <div>
        <img width="327" height="327" src="https://petcalculator.com/wp-content/uploads/2021/04/cropped-petcalculator-logo-small.jpg">
          <p> The petcalculator.com password associated with this email has been reset<br>Your new password is:<strong>'.$new_password.'</strong><br><br><i>PetCalculator Admin.</i>
        </div>
        </div></p>'
        );
        $sendgrid = new \SendGrid($email_api);
        
        $response = $sendgrid->send($email);
        //print $response->statusCode() . "\n";
        //print_r($response->headers());
        //print $response->body() . "\n";
        $GLOBALS['email_success'] = true;
    }
    catch (Exception $e){
        $GLOBALS['email_success'] = false;
    }

}
