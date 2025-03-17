
document.addEventListener("DOMContentLoaded", function () {
    // クッキーから値を取得する関数
    function getCookie(name) {
        const cookies = document.cookie.split('; ');
        console.log(cookies);
        for (const cookie of cookies) {
            const [key, value] = cookie.split('=');
            if (key === name) {
                console.log(key);
                return decodeURIComponent(value);
            }
        }
        return null;
    }

    // クッキーから 'username' を取得して自動入力
    const username = getCookie('username');
    if (username) {
        const usernameInput = document.querySelector('#username');
        if (usernameInput) {
            usernameInput.value = username;
        }
    }
});

