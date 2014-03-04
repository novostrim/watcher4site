<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/

function header_encode( $str )
{
	$send_charset = 'utf-8';
   return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
}

function send_mail( $name_to, $email_to, $subject, $body,
                   $name_from, $email_from,
                   $plain = 'html' )
{
  $send_charset = 'utf-8';
  if ( $plain == 'html' )
  {
      $body = "<html><head><title>$subject</title></head><body>$body</body></html>";
  }
  $to = header_encode($name_to ). ' <' . $email_to . '>';
  $subject = header_encode($subject);
  $from =  header_encode($name_from).' <' . $email_from . '>';
  $headers = "From: $from\r\n";
  $headers .= "Content-type: text/$plain; charset=$send_charset\r\n";
  $headers .= "Mime-Version: 1.0\r\n";

  return mail($to, $subject, $body, $headers);
}


?>
