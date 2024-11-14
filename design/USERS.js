
//DASHBOARD JS
// Function to display the notification modal
function toggleNotification() {
    var modal = document.getElementById("notificationModal");
    modal.style.display = "block";
}

// Function to close the notification modal
function closeNotificationModal() {
    var modal = document.getElementById("notificationModal");
    modal.style.display = "none";
}

//SOLO JS
function togglePlatform(element) {
    var platformDiv = element.querySelector('.applicant-platform');
    if (platformDiv.style.display === 'none') {
        platformDiv.style.display = 'block';
    } else {
        platformDiv.style.display = 'none';
    }
}

//CANDIDATELIST JS

document.addEventListener('DOMContentLoaded', function() {
    const lastChosenOption = sessionStorage.getItem('lastChosenOption');
    if (lastChosenOption) {
        document.getElementById(lastChosenOption).style.display = 'block';
        document.querySelector(`.top-navbar a[data-content="${lastChosenOption}"]`).classList.add('active');
    } else {
        document.getElementById('candidateListContent').style.display = 'block';
        document.querySelector('.top-navbar a.Candidates').classList.add('active');
        // Since 'candidateListContent' is the default, show 'independentC' or 'partylistC' based on the last chosen option
        const lastOptionContent = sessionStorage.getItem('lastOptionContent');
        if (lastOptionContent === 'independentC') {
            document.getElementById('independentC').style.display = 'block';
            document.getElementById('independentRadio').checked = true;
        } else if (lastOptionContent === 'partylistC') {
            document.getElementById('partylistC').style.display = 'block';
            document.getElementById('partylistRadio').checked = true;
        } else {
            // Default to showing independent content and checking the independent radio button
            document.getElementById('independentC').style.display = 'block';
            document.getElementById('independentRadio').checked = true;
        }
    }
    
    // Event listener for top navbar links
    document.querySelectorAll('.top-navbar a').forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const contentId = this.getAttribute('data-content');
            document.querySelectorAll('.main-content > div').forEach(function(contentDiv) {
                contentDiv.style.display = 'none';
            });
            document.querySelectorAll('.top-navbar a').forEach(function(link) {
                link.classList.remove('active');
            });
            this.classList.add('active');
            document.getElementById(contentId).style.display = 'block';
            if (contentId !== 'candidateListContent') {
                sessionStorage.setItem('lastChosenOption', contentId);
            } else {
                sessionStorage.removeItem('lastChosenOption');
                // When clicking 'Candidate List', show the last chosen option's content
                const lastOptionContent = sessionStorage.getItem('lastOptionContent');
                if (lastOptionContent === 'independentC') {
                    document.getElementById('independentC').style.display = 'block';
                    document.getElementById('independentRadio').checked = true;
                } else if (lastOptionContent === 'partylistC') {
                    document.getElementById('partylistC').style.display = 'block';
                    document.getElementById('partylistRadio').checked = true;
                } else {
                    // Default to showing independent content and checking the independent radio button
                    document.getElementById('independentC').style.display = 'block';
                    document.getElementById('independentRadio').checked = true;
                }
            }
        });
    });
});

//Candidate List
const independentRadio = document.getElementById('independentRadio');
const partylistRadio = document.getElementById('partylistRadio');

// Get the content containers
const independentC = document.getElementById('independentC');
const partylistC = document.getElementById('partylistC');

// Add event listener for independent radio button
independentRadio.addEventListener('change', function() {
    if (this.checked) {
        // Show independent content and hide partylist content
        independentC.style.display = 'block';
        partylistC.style.display = 'none';
        sessionStorage.setItem('lastOptionContent', 'independentC');
    }
});

// Add event listener for partylist radio button
partylistRadio.addEventListener('change', function() {
    if (this.checked) {
        // Show partylist content and hide independent content
        partylistC.style.display = 'block';
        independentC.style.display = 'none';
        sessionStorage.setItem('lastOptionContent', 'partylistC');
    }
});


//Partylist Application

document.addEventListener("DOMContentLoaded", function() {
    var positionSelect = document.getElementById("positionPartylist");
    var membersOfficerContainer = document.getElementById("membersOfficerContainer");
    var membersOfficersContainer = document.getElementById("membersOfficers");
    
    positionSelect.addEventListener("change", function() {
        var selectedPosition = positionSelect.value;
        if (selectedPosition === "President") {
            membersOfficerContainer.style.display = "block";
            membersOfficersContainer.style.display = "block";
        } else {
            membersOfficerContainer.style.display = "none";
            membersOfficersContainer.style.display = "none";
        }
    });

});




//Voting Form
function showVoteModal() {
    var modal = document.getElementById("voteModal");
    modal.style.display = "block";
}

function closeVoteModal() {
    var modal = document.getElementById("voteModal");
    modal.style.display = "none";
}

function fetchVotingSchedule() {
    fetch('fetch_voting_schedule.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('timer').innerText = 'Voting schedule not found.';
            } else {
                const startDateTime = new Date(data.VotingStartDateTime);
                const endDateTime = new Date(data.VotingEndDateTime);
                updateCountdown(startDateTime, endDateTime);
            }
        })
        .catch(error => {
            console.error('Error fetching voting schedule:', error);
            document.getElementById('timer').innerText = 'Error loading schedule.';
        });
}

function updateCountdown(start, end) {
    const timer = document.getElementById('timer');
    const interval = setInterval(() => {
        const now = new Date().getTime();
        if (now < start.getTime()) {
            timer.innerText = 'Voting has not started yet.';
        } else if (now > end.getTime()) {
            timer.innerText = 'Voting has ended.';
            clearInterval(interval);
        } else {
            const remainingTime = end.getTime() - now;
            const days = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
            const hours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((remainingTime % (1000 * 60)) / 1000);
            timer.innerText = `Time remaining: ${days}d ${hours}h ${minutes}m ${seconds}s`;
        }
    }, 1000);
}

function toggleNotifications() {
    const notificationContainer = document.getElementById('notifications');
    notificationContainer.style.display = notificationContainer.style.display === 'block' ? 'none' : 'block';
}


function fetchNotifications() {
    fetch('../user/fetch_notification.php')
        .then(response => response.json())
        .then(notifications => {
            const notificationContainer = document.getElementById('notifications');
            notificationContainer.innerHTML = '';

            if (notifications.error) {
                console.error(notifications.error);
                return;
            }

            if (notifications.length === 0) {
                const noNotifElement = document.createElement('div');
                noNotifElement.classList.add('notification', 'centered-notification');
                noNotifElement.innerHTML = '<p>No notifications</p>';
                notificationContainer.appendChild(noNotifElement);
            } else {
                notifications.forEach(notification => {
                    const notifElement = document.createElement('div');
                    notifElement.classList.add('notification');
                    notifElement.innerHTML = `
                        <p>Type: ${notification.type}</p>
                        <p>Status: ${notification.status}</p>
                        ${notification.status === 'Declined' ? `<p>Reason: ${notification.reason}</p>` : ''}
                    `;
                    notificationContainer.appendChild(notifElement);
                });
            }

            const notificationCount = document.getElementById('notification-count');
            notificationCount.textContent = notifications.length;
            notificationCount.style.display = notifications.length > 0 ? 'block' : 'none';
        })
        .catch(error => console.error('Error fetching notifications:', error));
}

document.addEventListener('DOMContentLoaded', () => {
    fetchNotifications();
    fetchVotingSchedule();
});

