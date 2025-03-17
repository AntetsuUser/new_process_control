window.onload = (e)=>{

	let video  = document.createElement("video");
	let canvas = document.getElementById("canvas");
	let ctx    = canvas.getContext("2d");
	let flag   = false
	var element = document.getElementById('help-id-input');
	// let msg    = document.getElementById("msg");

	const userMedia = {video: {facingMode: "environment",frameRate: { ideal: 30, max: 31 }}};
	navigator.mediaDevices.getUserMedia(userMedia).then((stream)=>
	{

		video.srcObject = stream;
		video.setAttribute("playsinline", true);
		video.play();
		startTick();
		//カメラが起動したとこをlogに
		camera_boot_log("カメラ起動","成功",log_camera_url);
	}).catch((error) => {
		//カメラが起動したとこをlogに
		camera_boot_log("カメラ起動","失敗",log_camera_url);
	});

	function startTick()
	{
		// msg.innerText = "準備中";
		if(video.readyState === video.HAVE_ENOUGH_DATA){
			canvas.height = video.videoHeight + 10;
			canvas.width = video.videoWidth;
			ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
			let img = ctx.getImageData(0, 0, canvas.width, canvas.height);
			let code = jsQR(img.data, img.width, img.height, {inversionAttempts: "dontInvert"});
			if(flag == false)
			{
				if(code)
				{
					//QRの読み取りが成功したときlogに
					camera_boot_log("QR読み取り","成功",log_camera_url);
					if(code.data != "")
					{
						camera_boot_log("QRコードデータ",code.data,log_camera_url);
						console.log(code.data)
						// msg.innerText = code.data;// Data
						//モーダルで表示させて飛ばす
						drawRect(code.location);// Rect
						openModal(code.data)
					}
				}
			}
		}
		setTimeout(startTick, 0.03);
	}

	function drawRect(location){
		drawLine(location.topLeftCorner,     location.topRightCorner);
		drawLine(location.topRightCorner,    location.bottomRightCorner);
		drawLine(location.bottomRightCorner, location.bottomLeftCorner);
		drawLine(location.bottomLeftCorner,  location.topLeftCorner);
	}

	function drawLine(begin, end){
		ctx.lineWidth = 4;
		ctx.strokeStyle = "#FF3B58";
		ctx.beginPath();
		ctx.moveTo(begin.x, begin.y);
		ctx.lineTo(end.x, end.y);
		ctx.stroke();
	}
		// モーダル部分のjs
	const openModal = function(url) 
	{
		flag = true
		var str = url
		var result = '';
		var print_id = '';
		var numbers = 0
		for(var i=0; i<str.length; i++) 
		{
			// 1文字ずつ「result」に格納していく
			result = str.charAt( i );
			if(result == "=")
			{
				numbers = i
			}
		}
		print_id = str.substring(numbers +1);

		camera_boot_log("QR読み取り結果",print_id,log_camera_url);
		console.log(print_id);
		console.log(url)
		document.querySelector('#js-result').innerText = "ID="+print_id
		document.querySelector('#js-link').setAttribute('href', url)

		document.querySelector('#js-modal').classList.add('is-show')
		document.querySelector('#js-modal').classList.remove('fede_out')
		
	}
	//カメラからQRコードを読み取って開いた時のモーダルの開くボタン
	document.querySelector('#js-link').addEventListener('click', function (e) {
	camera_boot_log("QR","開く",log_camera_url);
    e.preventDefault(); // デフォルトの遷移をキャンセル
		element = document.getElementById('js-result');
		// 文字列から「ID＝」を削除
		var outputString = element.innerHTML.replace("ID=", "");
		console.log(outputString)
		$.ajax
        ({ 
            type: 'post', 
            url: '../../matching.php', 
            data: {'characteristic_id': outputString}, 
            async: false, 
            cache: false, 
            dataType: 'text', 
            scriptCharaset: 'utf-8', 
            success: function(data)
            { 
                console.log(data); 
				var jsonData = JSON.parse(data); 
                if(data != "[]")
                {
					//入力済みかどうか
					if(jsonData[0].input_complete_flag == "true")
					{
						document.querySelector('#id-not-found').classList.add('not-found-none')
						// let element_url = "https://processcontrol.antetsu-systems.com/result_input/?characteristic_id="+outputString;
						let element_url = "/qr/input_directions?characteristic_id="+outputString;
						// window.close();
						window.location.href =element_url
						window.location.href = e.target.getAttribute('href');
						element.value = ""
						document.querySelector('#help-ja-modal').classList.remove('is-show')
						document.querySelector('#help-ja-modal').classList.add('fede_out')
					}
					else
					{
						alert('この指示書は入力済みです。')
					}
                }
                else
                {
                    console.log("データなし");
					document.querySelector('#id-not-found').classList.remove('not-found-none')
                }
            }, 
            error: function()
            { 
                console.log('error'); 
                console.log("XMLHttpRequest : " + XMLHttpRequest.status);
                console.log("textStatus     : " + textStatus);
                console.log("errorThrown    : " + errorThrown.message);
            } 
        })
	});
	//カメラからQRコードを読み取って開いた時のモーダルの閉じるボタン
	document.querySelector('#js-modal-close')
		.addEventListener('click', () => {
			camera_boot_log("QR","閉じる",log_camera_url);
			document.querySelector('#js-modal').classList.add('fede_out')
			document.querySelector('#js-modal').classList.remove('is-show')
			flag = false
		})
	// QR読み込めずに入力する時のモーダルの表示、非表示
	document.getElementById("help_id").onclick = function() 
	{
		camera_boot_log("読み込めない時は","開く",log_camera_url);
		flag = true
		document.querySelector('#help-ja-modal').classList.add('is-show')
		document.querySelector('#help-ja-modal').classList.remove('fede_out')
	};
	//カメラを使わずにモーダルを開いたときの開くボタン
	document.getElementById("help-js-link").onclick = function() 
	{
		camera_boot_log("ヘルプ","開く",log_camera_url);
		element = document.getElementById('help-id-input');
		console.log(element.value)
		$.ajax
        ({ 
            type: 'post', 
            url: '../../matching.php', 
            data: {'characteristic_id': element.value}, 
            async: false, 
            cache: false, 
            dataType: 'text', 
            scriptCharaset: 'utf-8', 
            success: function(data)
            { 
                console.log(data); 
				var jsonData = JSON.parse(data); 
                if(data != "[]")
                {
					console.log(jsonData[0].input_complete_flag)
					//入力済みかどうか
					if(jsonData[0].input_complete_flag == "true")
					{
						camera_boot_log("ヘルプ","閉じる",log_camera_url);
						document.querySelector('#id-not-found').classList.add('not-found-none')
						// let element_url = "https://processcontrol.antetsu-systems.com/result_input/?characteristic_id="+element.value;
						let element_url = "/qr/input_directions?characteristic_id="+element.value;
						// window.close();
						window.location.href = element_url
						element.value = ""
						document.querySelector('#help-ja-modal').classList.remove('is-show')
						document.querySelector('#help-ja-modal').classList.add('fede_out')
					}
					else
					{
						alert('この指示書は入力済みです。')
					}
                }
                else
                {
                    console.log("データなし");
					document.querySelector('#id-not-found').classList.remove('not-found-none')
                }
            }, 
            error: function()
            { 
                console.log('error'); 
                console.log("XMLHttpRequest : " + XMLHttpRequest.status);
                console.log("textStatus     : " + textStatus);
                console.log("errorThrown    : " + errorThrown.message);
            } 
        })
	};
	document.querySelector('#help-modal-close')
	.addEventListener('click', () => {
		camera_boot_log("ヘルプ","閉じる",log_camera_url);
		document.querySelector('#help-ja-modal').classList.add('fede_out')
		document.querySelector('#id-not-found').classList.add('not-found-none')
		document.querySelector('#help-ja-modal').classList.remove('is-show')
		element.value = ""
		flag = false
	})


	
	//カメラが起動したlogを送る
	function camera_boot_log(location,status,log_camera_url)
	{
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		console.log(log_camera_url);
		$.ajax({
			url: log_camera_url, // ルートで定義されたURL
			type: 'POST',
				data: {
					location: location, // キーと値のペアで送信
					status: status
				},
			dataType: 'json',
			success: function (response) {
				console.log('Log sent successfully:', response);
			},
			error: function (xhr, status, error) {
				console.error('Error sending log:', error);
				console.error('Error sending log:', xhr);
				console.error('Error sending log:', status);
				alert('エラーが発生しました。再試行してください。');
			}
		});
	}

}


