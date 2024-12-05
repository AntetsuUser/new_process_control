async function getLocalIPs() {
    const ipList = [];
    const peerConnection = new RTCPeerConnection({
        iceServers: []
    });
    peerConnection.createDataChannel('');
    await peerConnection.createOffer().then(offer => peerConnection.setLocalDescription(offer));

    return new Promise((resolve, reject) => {
        peerConnection.onicecandidate = event => {
            if (!event || !event.candidate) return;

            const candidate = event.candidate.candidate;
            const ipRegex = /([0-9]{1,3}\.){3}[0-9]{1,3}/;
            const ipMatch = ipRegex.exec(candidate);

            if (ipMatch) {
                const ip = ipMatch[0];
                const hiddenInput = document.querySelector('#local_ipaddress');
                const hiddenInput_2 = document.querySelector('#local_ipaddress_2');
                if (hiddenInput && !ipList.includes(ip) && hiddenInput_2) {
                    ipList.push(ip);
                    // hiddenInput.value = ip;
                    // デバックでIPを設定する
                    hiddenInput.value = "192.168.5.90";
                    hiddenInput_2.value = "192.168.5.90";
                    console.log('ローカルIPアドレス:', ip);
                    resolve();  // IPアドレスが設定されたらPromiseを解決
                }
            } else {
                reject('IPアドレスが取得できませんでした。');
            }
        };

        // タイムアウトを設けて、一定時間内にIPが取得できなかった場合はreject
        setTimeout(() => {
            reject('タイムアウト: IPアドレスが取得できませんでした。');
        }, 5000);  // 3秒後にタイムアウト
    });
}

$(document).ready(function() {
    $('.ip_form').on('submit', async function(e) {
        e.preventDefault();  // フォーム送信を一旦キャンセル
        console.log("フォーム送信をキャンセル");

        try {
            await getLocalIPs();  // IPアドレスが設定されるまで待機
            console.log("IPアドレスが設定されました");

            // IPアドレスが設定されたらフォームを手動で送信
            // const hiddenInput = document.querySelector('#local_ipaddress');
            const hiddenInput = document.querySelector('#local_ipaddress');
            const hiddenInput_2 = document.querySelector('#local_ipaddress_2');
            if (hiddenInput.value !== 'aaa') {
                this.submit();
            }else if(hiddenInput_2.value !== 'aaa')
            {
                this.submit();
            }
             else {
                alert('IPアドレスがまだ設定されていません。');
            }
        } catch (error) {
            console.error(error);
            alert('エラー: ' + error);
        }
    });
});
