// Get the modal elements
var loginModal = document.getElementById("loginModal");
var registerModal = document.getElementById("registerModal");

// Get the modal elements
var loginModal = document.getElementById("loginModal");
var registerModal = document.getElementById("registerModal");

// Get the buttons/links that open the modal
var loginButton = document.getElementById("loginButton");
var registerLink = document.getElementById("registerLink");

// Get the <span> element that closes the modal
var closeButtons = document.getElementsByClassName("close");

// Function to open modal
function openModal(modal) {
    modal.style.display = "block";
}

// Function to close modal
function closeModal(modal) {
    modal.style.display = "none";
}

// Event listeners for opening modals
loginButton.addEventListener("click", function() {
    openModal(loginModal);
});

registerLink.addEventListener("click", function() {
    openModal(registerModal);
});

// Event listeners for closing modals
for (var i = 0; i < closeButtons.length; i++) {
    closeButtons[i].addEventListener("click", function() {
        closeModal(this.parentElement.parentElement);
    });
}

document.getElementById('showPasswordIcon').addEventListener('click', function() {
    var passwordInput = document.getElementById('password');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        this.classList.remove('fa-eye');
        this.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        this.classList.remove('fa-eye-slash');
        this.classList.add('fa-eye');
    }
});


document.getElementById('showRegisterPasswordIcon').addEventListener('click', function() {
    var passwordInput = document.getElementById('registerPassword');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        this.classList.remove('fa-eye');
        this.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        this.classList.remove('fa-eye-slash');
        this.classList.add('fa-eye');
    }
});

 // Function to display notification modal
 function showNotificationModal(message) {
    var modal = document.getElementById("notificationModal");
    var modalText = document.getElementById("notificationText");
    modalText.innerHTML = message;
    modal.style.display = "block";
}

// Close the modal when the user clicks on (x)
var closeButtons = document.getElementsByClassName("close");
for (var i = 0; i < closeButtons.length; i++) {
    closeButtons[i].onclick = function() {
        var modal = this.parentElement.parentElement;
        modal.style.display = "none";
    }
}

// Close the modal when the user clicks anywhere outside of it
window.onclick = function(event) {
    var modals = document.getElementsByClassName("modal");
    for (var i = 0; i < modals.length; i++) {
        if (event.target == modals[i]) {
            modals[i].style.display = "none";
        }
    }
}

// Display notification modal when page loads if notificationMessage is set
window.onload = function() {
    if (typeof notificationMessage !== 'undefined') {
        showNotificationModal(notificationMessage);
    }
};


function formatStudentNumber(input) {
    let numericValue = input.value.replace(/\D/g, ''); 

    if (numericValue.length > 2) {
        numericValue = numericValue.slice(0, 2) + '-' + numericValue.slice(2); 
    }

    if (numericValue.length > 8) {
        numericValue = numericValue.slice(0, 8);
    }

    input.value = numericValue;
}

// Function to clear input fields
function clearInputFields() {
    document.getElementById("loginStudentNumber").value = "";
    document.getElementById("password").value = "";
    document.getElementById("registerStudentNumber").value = "";
    document.getElementById("registerPassword").value = "";
    document.getElementById("verificationCode").value = "";
    document.getElementById("fullName").value = "";
    document.getElementById("Email").value = "";
    document.getElementById("passWord").value = "";
    document.getElementById("dot-1").checked = false; // Uncheck gender options
    document.getElementById("dot-2").checked = false;
    document.getElementById("dot-3").checked = false;
}

// Event listener for login button
loginButton.addEventListener("click", function() {
    clearInputFields(); // Clear input fields

});

// Event listener for register link
registerLink.addEventListener("click", function() {
    clearInputFields(); // Clear input fields
  
});

registerLink.addEventListener("click", function() {
    // Set the showRegisterModal variable to true
    showRegisterModal = true;
    // Open the registration modal
    openModal(registerModal);
});

function fetchFullName() {
    var registerStudentNumber = document.getElementById("registerStudentNumber").value;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../connection/get_fullname.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById("fullName").value = xhr.responseText;
        }
    };
    // Send the request with proper data format
    xhr.send("registerStudentNumber=" + registerStudentNumber);
}

registerLink.addEventListener("click", function() {
    // Open the registration modal
    openModal(registerModal);
});


function sendOTP() {
    var email = document.getElementById("Email").value;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../connection/send_otp.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert(xhr.responseText); // Alert the response (for testing)
        }
    };
    // Send the request with the email data
    xhr.send("email=" + email);
}

function storeInputValues() {
    var studentNumber = document.getElementById('registerStudentNumber').value;
    var fullName = document.getElementById('fullName').value;
    var email = document.getElementById('Email').value;
    var password = document.getElementById('registerPassword').value; // Get the password value
    var confirmedPassword = document.getElementById('passWord').value; // Get the confirmed password value
    var institute = document.querySelector('input[name="Institute"]:checked').value; // Get the selected institute value

    sessionStorage.setItem('studentNumber', studentNumber);
    sessionStorage.setItem('fullName', fullName);
    sessionStorage.setItem('email', email);
    sessionStorage.setItem('password', password); // Store the password value
    sessionStorage.setItem('confirmedPassword', confirmedPassword); // Store the confirmed password value
    sessionStorage.setItem('institute', institute); // Store the institute value
}

// Function to populate form fields with stored values from sessionStorage
function populateFormFields() {
    document.getElementById('registerStudentNumber').value = sessionStorage.getItem('studentNumber') || '';
    document.getElementById('fullName').value = sessionStorage.getItem('fullName') || '';
    document.getElementById('Email').value = sessionStorage.getItem('email') || '';
    document.getElementById('registerPassword').value = sessionStorage.getItem('password') || ''; // Retrieve the password value
    document.getElementById('passWord').value = sessionStorage.getItem('confirmedPassword') || ''; // Retrieve the confirmed password value
    var instituteValue = sessionStorage.getItem('institute');
    // Check if there's a stored institute value and set the corresponding radio button as checked
    if (instituteValue) {
        document.querySelector('input[name="Institute"][value="' + instituteValue + '"]').checked = true;
    }
}

// Call the populateFormFields function when the page loads
window.onload = function() {
    populateFormFields();
};

function validatePasswords() {
    var password = document.getElementById("registerPassword").value;
    var confirmPassword = document.getElementById("passWord").value;

    if (password !== confirmPassword) {
        alert("Password and confirm password do not match.");
        return false;
    }
    return true;
}
function validateForm() {
    var instituteSelected = false;
    var instituteRadios = document.getElementsByName("Institute");

    for (var i = 0; i < instituteRadios.length; i++) {
        if (instituteRadios[i].checked) {
            instituteSelected = true;
            break;
        }
    }

    if (!instituteSelected) {
        alert("Please select your institute.");
        return false; // Prevent form submission
    }

    // If all validations pass, form submission continues
    return true;
}