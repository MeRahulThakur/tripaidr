<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
// user role
if (!function_exists('role')) {
    function role($param=false)
    {
        $role=array(
			1=>'admin',
			2=>'consumer',
			3=>'provider',
		);
        return ($param)?$role[$param]:$role;
    }
}
/**
 * passwordHash
 *
 * generate password
 *
 */
if (!function_exists('passwordHash')) {
    function passwordHash($param)
    {
        $options = [
            'cost' => 15
        ];
        return password_hash($param, PASSWORD_BCRYPT, $options);
    }
}

/**
 * sendMail
 */
if (!function_exists('sendMail')) {
    function sendMail($to = array(), $subject, $message, $smtp = false)
    {
        $CI = &get_instance();
        if ($smtp) {
            $config = array(
                'protocol' => 'smtp',
                'smtp_host' => 'ssl://smtp.googlemail.com',
                'smtp_port' => 465,
                'smtp_user' => 'xxx@gmail.com', // change it to yours
                'smtp_pass' => 'xxx', // change it to yours
                'mailtype' => 'html',
                'charset' => 'iso-8859-1',
                'wordwrap' => TRUE
            );
        } else {

            $config = array(
                'protocol' => 'sendmail',
                'mailpath' => '/usr/sbin/sendmail',
                'mailtype' => 'html',
                'charset' => 'iso-8859-1',
                'wordwrap' => TRUE
            );
        }
        $CI->load->library('email', $config);
        $CI->email->to($to);
        $CI->email->subject($subject);
        $CI->email->message($message);
        if ($CI->email->send()) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * random_str
 *
 */
if (!function_exists('random_str')) {

    function random_str($chars)
    {
        $data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($data), 0, $chars);
    }
}

// pre
function pre($data, $exit = false) {
    echo "<pre>";
    print_r($data);
    if ($exit) {
        exit;
    }
}

if (!function_exists('comma_error')) {

    function comma_error($string)
    {
        return trim(preg_replace( "/\n/", ",",strip_tags($string)),',');
    }
}

if (!function_exists('sendGrid')) {
    function sendGrid($toEmail,$subject,$content,$type=false){
        require APPPATH . 'libraries/sendgrid/vendor/autoload.php'; 
        $email = new \SendGrid\Mail\Mail(); 
        $email->setFrom("tripaider2020@gmail.com", "Tripaider");
        $email->setSubject($subject);
        $email->addTo($toEmail);
        if($type=='html'){
            $email->addContent(
                "text/html",$content
            );
        }
        else{
            $email->addContent("text/plain", $content);
        }
        //$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $sendgrid = new \SendGrid('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');
        try {
            $response = $sendgrid->send($email);
           return $response->statusCode() ;
        } catch (Exception $e) {
           return 'Caught exception: '. $e->getMessage() ."\n";
        }
    }
    
}




