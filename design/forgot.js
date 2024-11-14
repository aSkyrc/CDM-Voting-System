function sendOTP() {
    var emailrecovery = document.getElementById("email-box").value;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../connection/send_otp.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert(xhr.responseText); // Alert the response (for testing)
        }
    };
    // Send the request with the email data
    xhr.send("email=" + encodeURIComponent(emailrecovery));
}