 // モーダルの開閉処理
    $(document).ready(function() {
        const $modal = $('.js_modal');
        const $open = $('.js_modal_open');
        const $close = $('.js_modal_close');

        function modalOpen() {
            //加工数
            let processing_value = parseInt($('[name="processing"]').val(), 10) || 0;
            //良品
            let good_product_value = parseInt($('[name="good_product"]').val(), 10) || 0;
            //加工不良
            let poor_processing_value = parseInt($('[name="poor_processing"]').val(), 10) || 0;
            //材料不良
            let poor_material_value = parseInt($('[name="poor_material"]').val(), 10) || 0;

            let processing_sum = good_product_value + poor_processing_value + poor_material_value;

            let process = $('[name="process"]').val()
            switch (true) {
                case process.toString().includes('102') && process.toString().includes('MC'):
                    process_number = 2;
                    break;
                case process.toString().includes('102'):
                    process_number = 1;
                    break;
                case process.toString().includes('103') && process.toString().includes('MC'):
                    process_number = 4;
                    break;
                case process.toString().includes('103'):
                    process_number = 3;
                    break;
                case process.toString().includes('組立'):
                    process_number = 5;
                    break;
                case process.toString().includes('704'):
                    process_number = 6;
                    break;
                default:
                    process_number = 0; // エラー処理など、デフォルトの場合の設定
                    break;
            }
            $('[name="process_number"]').val(process_number)
            if (processing_value === processing_sum) {


                $modal.addClass('is-active');

                //trの要素を取得
                //加工数
                let $processing_number = $('#processing_number').closest('tr');
                //良品
                let $good_item = $('#good_item').closest('tr');
                //加工不良
                let $processing_defect = $('#processing_defect').closest('tr');
                //材料不良
                let $material_defect = $('#material_defect').closest('tr');

                //加工数
                let $tdProcessingNumber = $('<td></td>').text(processing_value).addClass('fix_value');
                $processing_number.append($tdProcessingNumber);
                //良品
                let $tdGoodItem = $('<td></td>').text(good_product_value).addClass('fix_value');
                $good_item.append($tdGoodItem);
                //加工不良
                let $tdProcessingDefect = $('<td></td>').text(poor_processing_value).addClass('fix_value');
                $processing_defect.append($tdProcessingDefect);
                //材料不良
                let $tdMaterialDefect = $('<td></td>').text(poor_material_value).addClass('fix_value');
                $material_defect.append($tdMaterialDefect);
            } else {
                alert("入力した値が不正です。\n入力した値を確認してください");
            }
        }

        $open.on('click', modalOpen);

        function modalClose() {
            $modal.removeClass('is-active');
            $('.fix_value').remove();
        }

        $close.on('click', modalClose);

        function modalOut(e) {
            if ($(e.target).is($modal)) {
                $modal.removeClass('is-active');
                $('.fix_value').remove();
            }
        }

        $(document).on('click', modalOut);

        // Enterキーによるフォーム送信を防止
        $("input").on("keydown", function(e) {
            if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
                return false;
            } else {
                return true;
            }
        });
    });

    function undisabled() {
        //formタグを変数に格納
        var todoform = document.querySelector('.form');
        //加工数
        let processing = document.querySelector('[name="processing"]');
        let processing_value = parseInt(processing.value, 10);
        console.log(processing_value)
        //良品
        let good_product = document.querySelector('[name="good_product"]');
        let good_product_value = parseInt(good_product.value, 10);
        if (isNaN(good_product_value)) {
            good_product_value = 0;
        }
        console.log(good_product_value)
        //加工不良
        let poor_processing = document.querySelector('[name="poor_processing"]');
        let poor_processing_value = parseInt(poor_processing.value, 10);
        if (isNaN(poor_processing_value)) {
            poor_processing_value = 0;
        }
        console.log(poor_processing_value);
        //材料不良
        let poor_material = document.querySelector('[name="poor_material"]');
        let poor_material_value = parseInt(poor_material.value, 10);
        if (isNaN(poor_material_value)) {
            poor_material_value = 0;
        }
        console.log(poor_material_value);
        let processing_sum = good_product_value + poor_processing_value + poor_material_value
        if (processing_value == processing_sum) {
            todoform.removeEventListener('submit', preventDefaultHandler);
            console.log("正しい")
            $('input[name="item_name"]').prop('disabled', false);
            $('input[name="item_code"]').prop('disabled', false);
            $('input[name="process"]').prop('disabled', false);
            $('input[name="workcenter"]').prop('disabled', false);
            $('input[name="deadline"]').prop('disabled', false);
        }
    }

    // フォーム送信時の防止イベントリスナー
    function preventDefaultHandler(event) {
        event.preventDefault();
    }

    