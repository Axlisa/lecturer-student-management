document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('toggle-theme');

    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function applyDarkMode() {
        document.documentElement.classList.add('dark-mode');
        document.body.style.backgroundColor = "#0B0C10";
        document.body.style.color = "#C5C6C7";
        document.querySelector('.navbar').style.backgroundColor = "#1F2833";
        document.querySelectorAll('.navbar li a, .navbar li button').forEach(function(el) {
            el.style.color = "#C5C6C7";
        });
        document.querySelectorAll('.navbar li a:hover:not(.active), .navbar li button:hover').forEach(function(el) {
            el.style.backgroundColor = "#45A29E";
            el.style.color = "#FEFFFF";
        });
        document.querySelectorAll('.navbar li a.active').forEach(function(el) {
            el.style.backgroundColor = "#66FCF1";
            el.style.color = "#0B0C10";
        });
        document.querySelector('.welcome-box').style.backgroundColor = "#66FCF1";
        document.querySelector('.welcome-box').style.color = "#0B0C10";
        document.querySelector('.toggle-box').style.backgroundColor = "#66FCF1";
        document.querySelector('.toggle-box').style.color = "#0B0C10";
        document.querySelector('.container').style.backgroundColor = "#1F2833";
        document.querySelector('h1').style.color = "#66FCF1";
        document.querySelectorAll('.button, input[type="submit"]').forEach(function(el) {
            el.style.backgroundColor = "#66FCF1";
            el.style.color = "#0B0C10";
        });
        document.querySelectorAll('.button:hover, input[type="submit"]:hover').forEach(function(el) {
            el.style.backgroundColor = "#45A29E";
        });
        document.querySelectorAll('table, th, td').forEach(function(el) {
            el.style.borderColor = "#444";
        });
        document.querySelectorAll('th').forEach(function(el) {
            el.style.backgroundColor = "#66FCF1";
        });
        document.querySelectorAll('td').forEach(function(el) {
            el.style.backgroundColor = "#1F2833";
        });
    }

    function applyLightMode() {
        document.documentElement.classList.remove('dark-mode');
        document.body.style.backgroundColor = "#3AAFA9";
        document.body.style.color = "";
        document.querySelector('.navbar').style.backgroundColor = "#DEF2F1";
        document.querySelectorAll('.navbar li a, .navbar li button').forEach(function(el) {
            el.style.color = "#17252A";
        });
        document.querySelectorAll('.navbar li a:hover:not(.active), .navbar li button:hover').forEach(function(el) {
            el.style.backgroundColor = "#2B7A78";
            el.style.color = "#FEFFFF";
        });
        document.querySelectorAll('.navbar li a.active').forEach(function(el) {
            el.style.backgroundColor = "#2B7A78";
            el.style.color = "#FEFFFF";
        });
        document.querySelector('.welcome-box').style.backgroundColor = "#2B7A78";
        document.querySelector('.welcome-box').style.color = "#FEFFFF";
        document.querySelector('.toggle-box').style.backgroundColor = "#2B7A78";
        document.querySelector('.toggle-box').style.color = "#FEFFFF";
        document.querySelector('.container').style.backgroundColor = "#DEF2F1";
        document.querySelector('h1').style.color = "#17252A";
        document.querySelectorAll('.button, input[type="submit"]').forEach(function(el) {
            el.style.backgroundColor = "#2B7A78";
            el.style.color = "#FEFFFF";
        });
        document.querySelectorAll('.button:hover, input[type="submit"]:hover').forEach(function(el) {
            el.style.backgroundColor = "#17252A";
        });
        document.querySelectorAll('table, th, td').forEach(function(el) {
            el.style.borderColor = "#ccc";
        });
        document.querySelectorAll('th').forEach(function(el) {
            el.style.backgroundColor = "#2B7A78";
        });
        document.querySelectorAll('td').forEach(function(el) {
            el.style.backgroundColor = "#DEF2F1";
        });
    }

    function checkTheme() {
        const theme = getCookie('theme');
        if (theme === 'dark-mode') {
            applyDarkMode();
        } else {
            applyLightMode();
        }
    }

    toggleButton.addEventListener('click', () => {
        if (document.documentElement.classList.contains('dark-mode')) {
            applyLightMode();
            setCookie('theme', 'light-mode', 365);
        } else {
            applyDarkMode();
            setCookie('theme', 'dark-mode', 365);
        }
    });

    checkTheme();
});
