document.addEventListener("DOMContentLoaded", function() {
    const notifToggle = document.getElementById('notifToggle');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifList = document.getElementById('notifList');
    const notifCount = document.getElementById('notifCount');

    notifToggle.addEventListener('click', function(e) {
        e.preventDefault();
        notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
    });

    function renderNotifications() {
        notifList.innerHTML = '';
        let unreadCount = 0;

        notifications.forEach(notif => {
            if(notif.status === 'unread') unreadCount++;

            const li = document.createElement('li');
            li.className = notif.status === 'unread' ? 'unread' : '';
            li.dataset.id = notif.id;
            li.innerHTML = `
                <span>${notif.message}</span>
                <i class="fas fa-times close-btn"></i>
            `;
            notifList.appendChild(li);
        });

        notifCount.textContent = unreadCount > 0 ? unreadCount : '';
    }

    renderNotifications();

    notifList.addEventListener('click', function(e) {
        if(e.target.classList.contains('close-btn')) {
            const li = e.target.closest('li');
            const notifId = li.dataset.id;

            fetch('mark_read.php', {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'id='+notifId
            }).then(res=>res.text())
              .then(()=>{
                  li.remove();
                  const index = notifications.findIndex(n => n.id == notifId);
                  if(index > -1) notifications.splice(index,1);
                  renderNotifications();
              });
        }
    });
});
 
