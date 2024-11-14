<?php
$showLoginModal = false;
$showRegisterModal = false;

if (!isset($_SESSION)) {
    session_start();
}

include("../connection/connection.php");
$con = connection();

if (isset($_POST['btnRegister'])) {
    $student_number = $_POST['StudentNumber'];
    $fullname = $_POST['Fullname'];
    $institute = $_POST['Institute'];
    $email = $_POST['Email'];
    $password = $_POST['Password'];
    $entered_otp = $_POST['VerificationCode'];

    // Check if the email exists in enrolled_students table
    $sqlCheckEnrollment = "SELECT * FROM enrolled_students WHERE email = '$email'";
    $resultCheckEnrollment = mysqli_query($con, $sqlCheckEnrollment);

    if (mysqli_num_rows($resultCheckEnrollment) > 0) {
        $row = mysqli_fetch_assoc($resultCheckEnrollment);
        $enrolled_student_number = $row['student_number'];
        $enrolled_fullname = $row['fullname'];
        $enrolled_institute = $row['institute'];

        // Verify that the details align with enrolled_students
        if ($student_number != $enrolled_student_number || $fullname != $enrolled_fullname || $institute != $enrolled_institute) {
            echo "<script> alert('The provided student details do not match the enrolled student. Please enter valid information.'); </script>";
            echo "<script>showRegisterModal();</script>"; // Show the register modal
            echo '<script>
                    document.getElementById("registerStudentNumber").value = "' . $student_number . '";
                    document.getElementById("fullName").value = "' . $fullname . '";
                    document.querySelector("input[name=\'Institute\'][value=\'' . $institute . '\']").checked = true;
                    document.getElementById("Email").value = "' . $email . '";
                  </script>';
        } else {
            // Check if entered OTP matches the stored OTP
            $sqlCheckOTP = "SELECT * FROM registered_students WHERE email = '$email' AND otp_code = '$entered_otp'";
            $resultCheckOTP = mysqli_query($con, $sqlCheckOTP);

            if (mysqli_num_rows($resultCheckOTP) > 0) {
                // Hash the password before storing it
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Update other fields in registered_students table
                $registration_date = date("Y-m-d H:i:s");
                $sqlUpdateRegistered = "UPDATE registered_students SET student_number = '$student_number', fullname = '$fullname', institute = '$institute', password = '$hashed_password', account_status = 'REGISTERED', regdate = '$registration_date', otp_code = '' WHERE email = '$email'";
                mysqli_query($con, $sqlUpdateRegistered);

                echo "<script> alert('Congratulations! you are officially registered.');</script>";
            } else {
                echo "<script> alert('Invalid OTP code. Registration failed.'); </script>";
            }
        }
    } else {
        // Email not found in enrolled_students
        echo "<script> alert('Email not found in enrolled students. Please enter a valid email.'); </script>";
    }
}

if (isset($_POST['btnLogin'])) {
    // Login form submission
    $student_id = $_POST['StudentNumber'];
    $password = $_POST['Password'];
    $sqlLogin = "SELECT * FROM registered_students WHERE student_number = '$student_id'";
    $resultLogin = mysqli_query($con, $sqlLogin);
    $row = mysqli_fetch_assoc($resultLogin);
    $total = mysqli_num_rows($resultLogin);

    if ($total > 0 && password_verify($password, $row['password'])) {
        $_SESSION['UserLogin'] = $row['id'];
        $_SESSION['UserLogin'];
        // Show login successful message
        echo "<script> alert('Login Successful') </script>";
        // Delay redirection for a few seconds to display the message
        echo "<script> setTimeout(function() { window.location.href = 'dashboard.php'; }, 1500); </script>";
    } else {
        // Invalid login, show error message and keep login modal open
        $showLoginModal = true;
        echo "<script> alert('Incorrect Student Number or Password') </script>";
        echo "<script>showModal();</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Background/LOGOSSC.png" type="Image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-GL/6Tt9iqaZs9uEeovQfZrrEZViKl+qGYqDz1LchK8P2Owlbh47twsZQxS62WO5+Dc+EF1ePsd0lgS13Q0Qrxkg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../design/landing.css">
    <link rel="stylesheet" href="../design/notification.css">
    <title>CDMSSCVMS</title>
</head>
<body>
        <nav>   

            <div class="LogoContainer">

                <img src="../Background/LOGOCDM.png" width="70px">
                <h2 class="brand-title">Colegio De Montalban</h2>

            </div>

            <div class="LinkContainer">

                <a class="lbutton" href="#" id="loginButton">Login</a>

            </div>

        </nav> 
      


        <div class="LandingContainer">
           
            <div class="left">
                <h1>Welcome,<span class="cdmians">CDMians!</span></h1>
                <p>Dear students, your voice matters! The future direction of our school community is in your hands, and participating in the supreme student council election is your opportunity to make a difference.</p>

                <h2>Not registered? <a href="#" id="registerLink">Click Here</a></h2> 
            </div>

            <div class="right">
                <img src="../Background/VoterImage.png" alt="Voter Image">
            </div>

        </div>


        


<div id="loginModal" class="modal" <?php if ($showLoginModal) echo 'style="display: block;"'; ?>>
    <div class="lmodal-content">
        <span class="close">&times;</span>
        <h2 style="text-align: center;"> Login</h2>
        <form method="post">
            
            <div class="input-row">
                    <div class="input-box">
                        <span class="details">Student Number</span>
                        <input type="text" id="loginStudentNumber" name="StudentNumber" pattern="\d{2}-\d{5}" placeholder="Enter your student number" required oninput="formatStudentNumber(this)">
                    </div>

                <div class="input-box">
                    <span class="details">Password</span>
                    <div class="password-container">
                        <input type="password" name="Password" id="password" placeholder="Enter your password" required>
                        <i class="fas fa-eye" id="showPasswordIcon"></i> <!-- Font Awesome icon for showing password -->
                    </div>
                </div>
            </div>
            <button type="submit" name="btnLogin" class="button">Login</button>
            <a class="forgot" href="forgotpassword.php">Forgot Password?</a> <!-- Forgot Password link -->

        </form>  
    </div>
</div>



<div id="registerModal" class="modal" <?php if ($showRegisterModal) echo 'style="display: block;"'; ?> >
    <div class="rmodal-content">
        <span class="close">&times;</span>
        <h2 style="text-align: center;" >Registration</h2>
            <form method="post" onsubmit="return validatePasswords() && validateForm()" action="../user/Landing.php">
                <div class="user-details">
 
                    <div class="input-row">
                        <div class="input-box">
                            <span class="details">Student Number</span>
                            <input type="text" id="registerStudentNumber" name="StudentNumber" placeholder="Enter your student number" required oninput="formatStudentNumber(this)" onkeyup="fetchFullName()">
                        </div>


                        <div class="input-box">
                            <span class="details">Full Name</span>
                            <input type="text" id="fullName" name="Fullname" placeholder="Enter your name"required> 
                        </div>               
                    </div>

                    <div class="input-row">
                        <div class="input-box">
                            <span class="details">Email</span>
                            <input type="text" id="Email" name="Email" placeholder="Enter your email" required> 
                        </div>

                        <div class="input-box">
                            <span class="details">Verification Code</span>
                            <div class="verification-box">
                                <input class="vtype" type="text" id="verificationCode" name="VerificationCode" placeholder="Enter verification code" required> 
                                <button class="vbutton" type="button" id="sendCodeButton" onclick="sendOTP()">Send Code</button>
                            </div>
                            <div class="error-message" id="email-error"></div>
                        </div>
                    </div>
                        
                    <div class="input-row">
                        <div class="input-box">
                            <span class="details">Password</span>
                            <div class="password-container">
                                <input type="password" name="Password" id="registerPassword" placeholder="Enter your password" required>
                                <i class="fas fa-eye" id="showRegisterPasswordIcon"></i> <!-- Font Awesome icon for showing password -->
                            </div>
                        </div>

                        <div class="input-box">
                            <span class="details">Confirm Password</span>
                            <input type="password" name="ConfirmPassword" id="passWord" placeholder="Confirm your password" required>
                        </div>
                    </div>

                </div>
                
                <div class="gender-details">
                    <span class="gender-title">Institute</span>
                    <input type="radio" name="Institute" id="dot-1" value="ICS">
                    <input type="radio" name="Institute" id="dot-2" value="IOB">
                    <input type="radio" name="Institute" id="dot-3" value="ITE">

                    <div class="category">
                        <label for="dot-1">
                            <span class="dot one"></span>
                            <span class="gender">ICS</span>
                        </label>
                        <label for="dot-2">
                            <span class="dot two"></span>
                            <span class="gender">IOB</span>
                        </label>
                        <label for="dot-3">
                            <span class="dot three"></span>
                            <span class="gender">ITE</span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" name="btnRegister" class="button" onclick="storeInputValues()">Register</button>
            </form>
</div>

<script src="../design/landings.js"></script>
</body>
</html>
