<?php 
       include("../connection/connection.php");

       $con = connection();
       
       if (isset($_POST['recover'])) {
        $email = $_POST['recover_email']; // Corrected to 'recover_email'
        $otp = $_POST['otp-recovery'];
        $newPassword = $_POST['Password'];
        $confirmPassword = $_POST['Confirm-Password'];
        
        // Check if OTP and email match for password recovery
        $sqlCheckOTP = "SELECT * FROM registered_students WHERE email = '$email' AND otp_rec = '$otp'";
        $resultCheckOTP = mysqli_query($con, $sqlCheckOTP);
        
        if (mysqli_num_rows($resultCheckOTP) > 0) {
            // Check if new passwords match
            if ($newPassword === $confirmPassword) {
                // Hash the new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password and reset OTP
                $sqlUpdatePassword = "UPDATE registered_students SET password = '$hashedPassword', otp_rec = NULL WHERE email = '$email'";
                mysqli_query($con, $sqlUpdatePassword);
        
                echo "<script>alert('Your Password is successfully updated.');</script>";
            } else {
                echo "<script>alert('Password do not match.');</script>";
            }
        } else {
            echo "<script>alert('Invalid OTP. Please, Double check in your email.');</script>";
        }
    }
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Background/LOGOSSC.png" type="Image/x-icon">
    <link rel="stylesheet" href="../design/forgots.css">
    <title>CDMSSCVMS</title>
</head>
<body>
    <div class="forgot-password-container">
        <h2>Account Recovery</h2>
        <form method="post">
            <div class="forgot-password-details">
                <div class="input-row">
                    <div class="input-box">
                        <span class="details">Email</span>
                        <input type="email" name="recover_email" id="email-box" placeholder="Enter your Email" required>
                    </div>
                    <div class="input-box">
                        <span class="details block-label">OTP</span>
                        <div class="otp-row">
                            <input type="text" name="otp-recovery" id="otp" placeholder="Enter your OTP code here">
                            <button type="button" class="otpbtn" name="otpbtn" onclick="sendOTPREC()">Send Code</button>
                        </div>
                    </div>
                    <div class="input-box">
                        <span class="details">New Password</span>
                        <input type="password" name="Password" id="Password" placeholder="Enter New Password" required>
                    </div>
                    <div class="input-box">
                        <span class="details">Confirm New Password</span>
                        <input type="password" name="Confirm-Password" id="Confirm-Password" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" class="confirm" name="recover">Recover</button>
                    <a class="btnlink" href="../user/Landing.php">Back to Landing.</a>
                </div>
            </div>
        </form>
    </div>
</body>
<script>
    function sendOTPREC() {
        var recover_email = document.getElementById("email-box").value;
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "../connection/send_otp.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert(xhr.responseText); // Alert the response (for testing)
            }
        };
        // Send the request with the email data
        xhr.send("recover_email=" + encodeURIComponent(recover_email)); // Change the parameter name to match PHP script
    }
</script>

</html>