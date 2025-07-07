function fetchNotifications() {
    fetch('includes/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const notificationCount = document.getElementById('notification-count');
            const notificationList = document.getElementById('notification-list');

            if (notificationCount) {
                notificationCount.textContent = data.unread_count ?? 0;
            }

            if (notificationList) {
                notificationList.innerHTML = '';

                if (data.notifications.length === 0) {
                    notificationList.innerHTML = '<div class="notification-item no-notifications">No new notifications</div>';
                } else {
                    data.notifications.forEach(notification => {
                        const item = document.createElement('div');
                        item.className = 'notification-item';
                        item.innerHTML = notification.message;

                        if (notification.is_read) {
                            item.classList.add('notification-read');
                        }

                        item.onclick = () => {
                            item.classList.add('notification-clicked');
                            setTimeout(() => {
                                markAsReadAndRedirect(notification.id, notification.redirect_url);
                            }, 300); 
                        };

                        notificationList.appendChild(item);
                    });
                }
            }
        })
        .catch(err => console.error('Error fetching notifications:', err));
}

function markAsReadAndRedirect(notificationId, redirectUrl) {
    fetch('includes/mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: notificationId })
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to mark as read');
        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
    })
    .catch(err => console.error('Error marking as read:', err));
}

function markAllAsRead() {
    fetch('includes/mark_all_notifications_read.php', {
        method: 'POST',
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to mark all as read');
        fetchNotifications();
    })
    .catch(err => console.error('Error marking all as read:', err));
}

document.addEventListener('DOMContentLoaded', () => {
    fetchNotifications();

    const markAllBtn = document.getElementById('mark-all-read-btn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', markAllAsRead);
    }
});

setInterval(fetchNotifications, 5000);