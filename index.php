<?php
/*
    AUTHOR: Moin Nadeem.
    TWITTER:  @thenextfowl.
    EMAIL:  moinnadeem@moinnadeem.com
    DATE: 6/30/12.
    PURPOSE:  To reset WP credentials for a certain subset of users, and email those users with the new credentials. 

    NOTES:  The code isn't very well commented, but I used parenthesis with capital letters inside eg. (LIKE THIS) to denote fields
      that need to be filled out (it should be easy). Feel free to contact me if you need help, otherwise the code is yours. It's 
      quick n' dirty, but I didn't see anything like this online so I created it. Initially written with PHP 5.5.
*/
set_time_limit(0);

require 'phpass-0.3/PasswordHash.php';

// initialize variable in global scope.
$hash;

// modified Matt Huggins' code on SO.
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return $result;
}

$con = mysqli_connect("(URL TO DATABASE)","(USERNAME)","(PASSWORD)","(DATABASE NAME)");

if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

$t_hasher = new PasswordHash(8, TRUE);

// for this particular quick use, I just cleared the password fields for the users who I wanted to reset.
$query = mysqli_query($con, "SELECT `user_email` FROM `wp_(RANDOM STRING)_users` WHERE `user_pass` = '';");

while($result = mysqli_fetch_array($query)) {
$password = generatePassword(8);

// hashes and salts the specified password.
do {
    $hash = $t_hasher->HashPassword($password);
    $check = $t_hasher->CheckPassword($password, $hash);
} while (!$check);

echo "$result[0]: ";
echo "$hash - $password\r\n";
mysqli_query($con, "UPDATE `wp_(RANDOM STRING)_users SET `user_pass` = '$hash' WHERE `user_pass` = '';");

$to = $result[0];
$subject = "2013 - 2014 Login Credentials: (WEBSITE URL)";

// compose headers
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= "From: (FROM EMAIL)\r\n";
$headers .= "Reply-To: (REPLY-TO EMAIL)\r\n";

$message = '(PUT CONTENTS OF HTML EMAIL HERE.)'

mail($to,$subject,$message,$headers,'-f (REPLY-TO EMAIL)');

// for the purpose of logging the new credentials. remove this bit of code if you don't want to do that.
$handle = fopen("log.txt", "a");
fwrite($handle, $result[0] . ": " . $password . "\n");
fclose($handle);

}

mysqli_close($con);