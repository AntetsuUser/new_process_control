$(document).ready(function() {
    // var userAgent = navigator.userAgent;
    // var platform = navigator.platform;

    // var isIpad = !!navigator.maxTouchPoints && navigator.maxTouchPoints > 1 && navigator.platform === 'MacIntel';

    // if (isIpad) {
    //     $("#monitor").hide()
    // }
    //  if (userAgent.match(/iPad/i)) {
    //     console.log("iPad");
    //     $(".aaa").text("iPad")
    // } else if (userAgent.match(/iPhone/i)) {
    //     console.log("iPhone");
    //     $(".aaa").text("iPhone")
    // } else if (userAgent.match(/Android/i)) {
    //     console.log("Android device");
    //     $(".aaa").text("Android")
    // } else if (userAgent.match(/Windows NT/i) || userAgent.match(/Macintosh/i)) {
    //     console.log("PC");
    //       $(".aaa").text("PC")
    // } else {
    //     console.log("Other device");
    //     $(".aaa").text("?")
    // }
//     // ローカルIPを取得してから処理を続行する
//     getLocalIPs(function(ip) {
//         console.log("取得したIP:", ip);

//         // .ajax-linkクラスのaタグに対してクリックイベントを設定
//         $('.ajax-link').on('click', function(e) {
//             e.preventDefault(); // 通常のリンク遷移をキャンセル

//             var post_url = $(this).attr('href'); // 元々のリンク先URLを取得
//             console.log(post_url);
//              // クリックしたaタグ内のpタグのテキストを取得
//             var p_text = $(this).find('p').text();
//             console.log('クリックされたボタンのテキスト:', p_text);
            
            
//             // Ajaxリクエストを実行
//             $.ajax({
//                 url: logRoute,
//                 type: 'POST',
//                 data: JSON.stringify({ 
//                     local_ip: ip,
//                     key: post_url,
//                     text: p_text 
//                 }),
//                 contentType: 'application/json',
//                 headers: {
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                 },
//                 success: function(response) {
//                     console.log('成功:', response);
//                     window.location.href = post_url; // Ajax成功時に元のリンク先へ遷移
//                 },
//                 error: function(xhr, status, error) {
//                     console.error('エラー:', error);
//                     alert('エラーが発生しました: ' + xhr.status + ' ' + xhr.statusText);
//                 }
//             });
//         });
//     });
});

// function getLocalIPs(callback) {
//     const ipList = [];
//     const peerConnection = new RTCPeerConnection({
//         iceServers: []
//     });
//     peerConnection.createDataChannel('');
//     peerConnection.createOffer().then(offer => peerConnection.setLocalDescription(offer));

//     peerConnection.onicecandidate = event => {
//         if (!event || !event.candidate) return;
//         const candidate = event.candidate.candidate;
//         const ipRegex = /([0-9]{1,3}\.){3}[0-9]{1,3}/;
//         const ip = ipRegex.exec(candidate)[0];

//         const hiddenInput = document.querySelector('input[name="local_ip"]');
//         if (!ipList.includes(ip)) {
//             ipList.push(ip);
//             hiddenInput.value = ip; // IPアドレスを更新
//             // console.log("取得したIP:", ip);
//             callback(ip); // IP取得後にコールバック関数を実行
//         }
//     };
// }
