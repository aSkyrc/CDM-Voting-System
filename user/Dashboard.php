<?php
include_once '../user/SideNav.php';
include_once("../connection/connection.php");
$conn = connection(); 

$query = "SELECT image_data, content FROM content_upload";
$result = $conn->query($query);

$announcements = '';

while ($row = $result->fetch_assoc()) {
    $imageData = base64_encode($row['image_data']);
    $content = htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8');
    $announcements .= "<div class='announcement'>
                        <div class='image'>
                            <img src='data:image/jpeg;base64,$imageData' alt='Image'>
                        </div>
                        <div class='content'>
                            $content
                        </div>
                    </div>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>
    <link rel="stylesheet" href="../design/USERS.css">
</head>
<body>
<nav class="top-navbar">
    <div id="notification-bell" onclick="toggleNotifications()">
        <img src="../Background/Bell.png" alt="Notification Bell">
        <div id="notification-count"></div>
        <div id="notifications"></div>
    </div>
</nav>

<div><h4>Announcement</h4></div>

<div id="countdown">
    <h3>Voting Schedule Countdown</h3>
    <p id="timer">Loading...</p>
</div>

<div class="announcements-container">
    <?php echo $announcements; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        fetchNotifications();
        fetchVotingSchedule();
    });

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

</script>
</body>
</html>