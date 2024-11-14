<?php
if (!isset($_SESSION)) {
    session_start();
}

require '../vendor/autoload.php';

include("../connection/connection.php");
$con = connection();

// Include Composer's autoloader
require '../vendor/autoload.php';

// Use the PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTPEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                            // Use SMTP
        $mail->Host = 'smtp.gmail.com';                            // Specify SMTP server
        $mail->SMTPAuth = true;                                      // Enable SMTP authentication
        $mail->Username = 'acdm509@gmail.com';                           // SMTP username
        $mail->Password = 'nwmj cwcd noiz vwsd';                           // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;             // Enable TLS encryption; use PHPMailer::ENCRYPTION_STARTTLS for insecure connections
        $mail->Port = 465;                                           // TCP port to connect to

        // Recipients
        $mail->setFrom('acdm509@gmail.com', 'CDMSSCVMS');
        $mail->addAddress($email);            // Add a recipient

        // Content
        $mail->isHTML(true);                                         // Set email format to HTML
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Your OTP code is $otp.";
        $mail->AltBody = "Your OTP code is $otp.";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}


if (isset($_POST['email'])) {
    $email = $_POST['email'];

    // Check if the email exists in enrolled_students
    $sqlCheckEnrollment = "SELECT * FROM enrolled_students WHERE email = '$email'";
    $resultCheckEnrollment = mysqli_query($con, $sqlCheckEnrollment);

    if (mysqli_num_rows($resultCheckEnrollment) > 0) {
        // Email exists in enrolled_students

        // Check if the student is already fully registered
        $sqlCheckFullRegistration = "SELECT * FROM registered_students WHERE email = '$email' AND account_status = 'REGISTERED'";
        $resultCheckFullRegistration = mysqli_query($con, $sqlCheckFullRegistration);

        if (mysqli_num_rows($resultCheckFullRegistration) > 0) {
            echo "The student is already registered.";
        } else {
            // Check if OTP has already been sent
            $sqlCheckOTP = "SELECT * FROM registered_students WHERE email = '$email'";
            $resultCheckOTP = mysqli_query($con, $sqlCheckOTP);

            if (mysqli_num_rows($resultCheckOTP) > 0) {
                echo "OTP code has already been sent to this email.";
            } else {
                // Generate OTP
                $otp = rand(100000, 999999);

                // Store the OTP in the registered_students table
                $sqlInsertOTP = "INSERT INTO registered_students (email, otp_code) VALUES ('$email', '$otp')";
                mysqli_query($con, $sqlInsertOTP);

                // Send OTP email
                sendOTPEmail($email, $otp);

                echo "OTP sent";
            }
        }
    } else {
        // Email is not enrolled
        echo "This email is not in database of enrolled students.";
    }
}


if (isset($_POST['recover_email'])) {
    $email = $_POST['recover_email'];

    // Check if email is registered
    $sqlCheckEmail = "SELECT * FROM registered_students WHERE email = '$email'";
    $resultCheckEmail = mysqli_query($con, $sqlCheckEmail);
    if (mysqli_num_rows($resultCheckEmail) > 0) {
        // Generate OTP
        $otp = rand(100000, 999999);

        // Store the OTP in the registered_students table
        $sqlInsertOTP = "UPDATE registered_students SET otp_rec = '$otp' WHERE email = '$email'";
        mysqli_query($con, $sqlInsertOTP);

        // Send OTP email
        sendOTPEmail($email, $otp);

        echo "OTP sent";
    } else {
        echo "This email is not in database of registered students.";
    }
}
?>