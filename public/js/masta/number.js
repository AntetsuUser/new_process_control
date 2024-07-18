$(document).ready(function() {
    // ページが読み込まれたとき

    //**************************↓品目一覧************************************************************************************************* */
    /////////////////////////////////////////////////
    // 工程一覧ボタンが押されたとき
    ///////////////////////////////////////////////
    $(document).on("click", ".modalOpen", function() {
        var itemId = $(this).data('id');
        var processingItem = $(this).data('processing-item');
        var childPartNumber1 = $(this).data('child-part-number1');
        var childPartNumber2 = $(this).data('child-part-number2');
        // 取得したitemIdをコンソールに出力（必要に応じて処理を追加）
        $('#parent_name').val(processingItem);
        $('#child_name1').val(childPartNumber1);
        $('#child_name2').val(childPartNumber2);
        let number;
        process_setting()
        if(processingItem != "")
        {
            number = 0
            process_get_ajax(processingItem,number);
        }
        if(childPartNumber1 != "")
        {
            number = 1
            process_get_ajax(childPartNumber1,number);
        }
         if(childPartNumber2 != "")
        {
            number = 2
            process_get_ajax(childPartNumber2,number);
        }
        //// モーダルを表示
        $('#easyModal').show();
    });

    /////////////////////////////////////////////////
    // モーダルを閉じるためのイベントハンドラ
    /////////////////////////////////////////////////
    $('.modalClose').on('click', function() {
        $('#easyModal').hide();
    });

    /////////////////////////////////////////////////
    // モーダル外をクリックしたときにモーダルを閉じる
    /////////////////////////////////////////////////
    $(window).on('click', function(event) {
        if ($(event.target).is('#easyModal')) {
            $('#easyModal').hide();
        }
    });
    function process_get_ajax(item_name,number)
    {   
        $.ajax({
            url: "https://192.168.3.96/ajax/masta/process_get_ajax.php",
            type: "POST",
            dataType: "json",
            data: { item_name: item_name },
        })
        .done(function(data) {
           data.forEach((element, index)=> {
                console.log(element)
                $('#process' + number +index ).val(element.process);
                $('#store' + number +index ).val(element.store);
                $('#time' + number +index ).val(element.processing_time);
                $('#lot' + number +index ).val(element.lot);
           });
        })
    }
    //モーダルの値の初期化
    function process_setting()
    {
        for (let number = 0; number < 3 ; number++) {
            $('#parent_name').val();
            for (let index = 0; index <=3 ; index++) {
                $('#process' + number + index ).val("");
                $('#store' + number + index ).val("");
                $('#time' + number +index ).val("");
                console.log( number + "" + index)
            }
        }
    }




    //**************************↓品目追加・更新の処理************************************************************************************************* */
    
    //selectが選択されたとき兄弟要素に選択された値を入れる
    $('select').change(function() {
        let val = $(this).val();
        $(this).prev().val(val);
    });

    /////////////////////////////////////////////////
    // 工場と製造課のselectが変更されたときの処理
    ///////////////////////////////////////////////
    $('select').change(function() {
        let change_select_id = $(this).attr('id');
        let factory_key = "factories_id";
        let department_key = "departments_id";
        if (change_select_id.includes(factory_key)) {
            // 工場が変更された場合の処理(工場idを渡す)
            factory_change(change_select_id);
        } else if (change_select_id.includes(department_key)) {
            // 製造課が変更された場合の処理(製造課idを渡す)
            department_change(change_select_id);
        }

    });


    ////////////////////////////////////////////
    // テキストエリアが選択されたときの処理
    ///////////////////////////////////////////
    $('input[type="text"]').on('focus', function() {

        let select_element_name = $(this).attr('name');
        let keyname = "process_store_"
        //選択したnameにkeynameが含まれていたら
        if(select_element_name.includes(keyname))
        {
            // すべての .input_window 要素を削除してリセット
            $('.input_window').remove();
            // 選択されたテキストエリアの一番近い親要素でクラスがmasta_numberであるものを見つけてくる
            let parent_container = $(this).closest('.masta_number');

            let select_element_value = $(this).val();

            text_area_selection(parent_container ,select_element_value, $(this))
        }
    });


    ////////////////////////////////////////////////
    // ラジオボタンが変更されたとき
    //////////////////////////////////////////////////
    $(document).on('change', 'input[type="radio"]', function() {
        if ($(this).is(':checked')) {

            let radio_value = $(this).val();
            // 選択されたラジオボタンの一番近い親要素でクラスがmasta_numberであるものを見つけてくる
            let parent_container = $(this).closest('.masta_number');
            let input_window = $(this).closest('.input_window');
            let text_box = $(this).closest('.masta_number_area').find('input[type="text"]');
            text_box.each(function(index, element) {
                var name = $(element).attr('name');
                let keyname = "process_store_";
                if(name.includes(keyname))
                {
                    //テキストボックスの所を初期化する
                    $(element).val("");
                }
            });
            let select_element_value = "";
            equipment_number_get(parent_container ,radio_value,input_window,select_element_value)
        }
    });

    ///////////////////////////////////////////////
    // 設備番号のチェックボックスが変更されたときの処理
    ////////////////////////////////////////////////
    $(document).on('change', 'input[type="checkbox"]', function() {
        checkbox_change($(this));
    });

    ////////////////////////////////////////////////
    // ストア・W/C選択の閉じるボタンが押されたときの処理
    //////////////////////////////////////////////////
    $(document).on("click", "#close_btn", function() {
        close_button($(this));
    });


    /////////////////////////////////////////
    // 工場が変更されたときの製造課をセレクトボックスに入れる関数　引数(工場selectボックスのid)
    ////////////////////////////////////////
    function factory_change(change_select_id) {
        // 親品番か子品番、どこの工場が選択されたかを取得するためインデックスを取得
        let index = parseInt(change_select_id.split("_")[2]);

        let select_id = $(`#${change_select_id}`);//idを取得
        
        // 関連する製造課の要素IDを構築
        let departments_id = `departments_id_${index}`;
        let factory_id = select_id.val();
        //選択された工場の名前を取得
        let factory_name = select_id.find("option:selected").text();
        // 工場の名前をinpputに入れる
        let factories_name_id =  `factories_name_${index}`;
        $(`#${factories_name_id}`).val(factory_name);

        // 製造課のオプションを初期化
        $(`#${departments_id} option:not(:first-child)`).remove();

        // 製造課のデータを取得してオプションを追加
        $.ajax({
            url: "https://192.168.3.96/ajax/masta/department_get.php",
            type: "POST",
            dataType: "json",
            data: { factory_id: factory_id },
        })
        .done(function(data) {
            // 取得したデータをオプションに追加
            data.forEach(element => {
                $("<option>").text(element.name).val(element.id).appendTo($(`#${departments_id}`));
            });
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            // エラーメッセージを表示
            alert("製造課取得のDB接続に失敗しました。\nシステム担当にご連絡ください。");
            console.error("Ajax失敗:", jqXHR, textStatus, errorThrown);
        });
    }
    /////////////////////////////////////////
    // 工場が変更されたときの製造課をセレクトボックスに入れる関数　引数(製造課selectボックスのid)
    ////////////////////////////////////////
    function department_change(change_select_id)
    {
        // 親品番か子品番、どこの工場が選択されたかを取得するためインデックスを取得
        let index = parseInt(change_select_id.split("_")[2]);
        let select_id = $(`#${change_select_id}`);//idを取得
        //選択された製造課の名前を取得
        let department_name = select_id.find("option:selected").text();
        // 工場の名前をinpputに入れる
        let department_name_id =  `departments_name_${index}`;
        $(`#${department_name_id}`).val(department_name);
    }

    /////////////////////////////////////////////////////////////////
    // テキストエリアがフォーカスされたときの処理(選択したinputがどこのエリアなのか,選択されたテキストボックスの値,選択したinputのname)
    ////////////////////////////////////////////////////////////////
    function text_area_selection(parent_container,select_element_value,this_input_element)
    {
        //選択されたエリアの製造課を取得してくる
        let department_id = parent_container.find('[name="departments_id[]"]').val();
        if(department_id)
        {
            //ストア・W/C選択するhtmlを挿入
            this_input_element.after(create_input_window_html());
            //ラジオボタンを取得してきて表示する
            lines_get( this_input_element.siblings('.input_window'),department_id,select_element_value)
            //ラジオボタンの個数
            let radio_length = this_input_element.siblings('.input_window').find('input[type="radio"]').length
            //ラジオボタンがあるかどうか調べる
            if(radio_length > 0)
            {
                //テキストエリアに値が入っているか
                if(select_element_value)
                {
                    //classを取得
                    let input_window = parent_container.find('.input_window');
                    //選択されてるラジオボタンのvalueを取得
                    let radio_value = input_window.find('input[type="radio"]:checked').val();
                    //選択されているラジオボタンでチェックボックスを取得して表示する
                    equipment_number_get(parent_container ,radio_value,input_window,select_element_value)
                    //ストア・W/C選択するhtmlをdisplaynoneからblockに変えて表示
                    this_input_element.siblings('.input_window').css('display', 'block');
                }else
                {
                    // 入っていなかった場合普通に表示
                    //ストア・W/C選択するhtmlをdisplaynoneからblockに変えて表示
                    this_input_element.siblings('.input_window').css('display', 'block');
                }

            }else{
                alert("マスタが登録されていません");
                this_input_element.blur();//←これがないと永遠にalertが出る
            }
        }
        else{
            alert("製造課が選択されていません");
            this_input_element.blur();//←これがないと永遠にalertが出る
        }
    }

    /////////////////////////////////////////////////////////////////
    // lineを取得してきてラジオボタンに設定する(選択された要素、製造課ID、選択された要素の値)
    ////////////////////////////////////////////////////////////////
    function lines_get(this_input_element,department_id,select_element_value)
    {
        //ラインが入る配列
        let line_arr = [];
        $.ajax({
            url: "https://192.168.3.96/ajax/masta/lineAndstore_get.php",
            type: "POST",
            dataType: "json",
            async: false,
            data: { department_id: department_id },
        })
        .done(function(data) {
            if (data.length > 0) 
            {
                // 取得したデータを配列に追加
                data.push({ line: 'ストア' });
                line_arr.push(...data);
            } else {
                // 空の配列の場合の処理
                console.log("配列は空です");

            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            // エラーメッセージを表示
            alert("ストア取得のDB接続に失敗しました。\nシステム担当にご連絡ください。");
            console.error("Ajax失敗:", jqXHR, textStatus, errorThrown);
        });
        //ラジオボタンを生成する場所
        let radio_area = this_input_element.find('.radio_area')
        //ラジオボタンを生成
        line_radio_create(radio_area,line_arr,select_element_value)
    }


    ////////////////////////////////////////////////
    // ラジオボタンを生成する関数(生成するエリア,データ配列,テキストボックスの値)
    //////////////////////////////////////////////////
    function line_radio_create(radio_area,line_arr,select_element_value)
    {
        let line_text ="";
        // テキストボックスに値があるか
        if(select_element_value)
        {
            //文字列をコンマで区切っては入れるになおす
            var select_input_array = select_element_value.split(',');
            select_input_array.forEach(element => {
                    if (element.includes("ストア")) {
                        // 文字列を分割する
                        var parts = element.match(/(ストア)(.+)/);
                        // "ストア" とその後の文字列を取得
                        line_text = parts[1]; // "ストア"
                    } else {
                        // 文字列を分割する
                        var parts = element.split(/(?=[0-9])/);
                        // 文字と数字を取得
                        line_text = parts[0]; // 文字
                    }
                })
        }
        //ラインの配列
        line_arr.forEach(item => {
            let line_name;
            let radio_input;

            if (item.line == 'ストア') {
                line_name = 'store';
            } else {
                line_name = item.line;
            }
            //テキストボックスに値があった場合同じテキストの所を選択状態にする
            if(line_text == item.line)
            {
                radio_input = $("<input>").attr({ type: "radio", name: "store_wc", id:line_name, value: line_name }).prop("checked", true);
            }else{
                radio_input = $("<input>").attr({ type: "radio", name: "store_wc", id:line_name, value: line_name });
            }
            let label = $("<label>").attr("for", line_name).text(item.line);
            let div = $("<div>").addClass("option").append(radio_input).append(label);
            radio_area.append(div);
        });
    }


    ////////////////////////////////////////////////
    // 設備番号のチェックボックスを生成する(親のクラス、ラジオボタンの値、チェックボックスを入れる親クラス、テキストボックスの値)
    //////////////////////////////////////////////////
    function equipment_number_get(parent_container ,radio_value,input_window,select_element_value)
    {
        //製造課idを取得
        let department_id = parent_container.find('[name="departments_id[]"]').val();
        //チェックボックスclassの要素を取得
        let checkbox_area = input_window.find('.checkbox_area');
        let line_text=[];
        //チェックボックスclassの要素を初期化する
        checkbox_area.empty();
        // テキストボックスに値があるか
        if(select_element_value)
        {
            var select_input_array = select_element_value.split(',');
            select_input_array.forEach(element => {
                if (element.includes("ストア")) {
                    // 文字列を分割する
                    var parts = element.match(/(ストア)(.+)/);
                    // "ストア" とその後の文字列を取得
                    numbers = parts[2]; // "Aなど"
                } else {
                    // 文字列を分割して数字部分を取得
                    var parts = element.match(/[0-9]+/);
                    if (parts && parts.length > 0) {
                        numbers = parts[0]; // 数字部分を取得
                    }
                    console.log(numbers)
                }
                line_text.push(numbers);
                // ラジオボタンの選択処理
            });
        }
        $.ajax({
            url: "https://192.168.3.96/ajax/masta/equipment_number_get.php",
            type: "POST",
            dataType: "json",
            async: false,
            data: {
                line: radio_value,
                department_id: department_id,
            }
        })
        .done(function(data) {
            data.forEach(item => {
                let line_number;
                let checkbox_input;
                if (radio_value == 'store') {
                    line_number = item.store;
                } else {
                    line_number = item.equipment_id;
                }
                //テキストボックスに値があった場合同じテキストの所を選択状態にする
                if (line_text.includes(line_number.toString())) {
                    checkbox_input = $("<input>").attr({ type: "checkbox", name: "line_number", id: line_number,  value: line_number }).prop("checked", true);
                } else {
                    checkbox_input = $("<input>").attr({ type: "checkbox", name: "line_number", id: line_number,  value: line_number });
                }
                const label = $("<label>").attr("for", line_number).text(line_number);
                const div = $("<div>").addClass("option").append(checkbox_input).append(label);
                checkbox_area.append(div);
            });
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            alert("ストア取得のDB接続に失敗しました。\nシステム担当にご連絡ください。");
            console.error("Ajax失敗:", jqXHR, textStatus, errorThrown);
        });
    }   
    ////////////////////////////////////////////////
    // 設備番号のチェックボックスが変更されたとき
    //////////////////////////////////////////////////
    function checkbox_change(checkbox_element) {
        let input_window_area = checkbox_element.closest('.input_window');
        let masta_number_area = checkbox_element.closest('.masta_number_area');
        let process_store = "process_store_";
        let input_name;

        masta_number_area.find('input').each(function() {
            let name = $(this).attr('name');
            if (name.includes(process_store)) {
                input_name = name;
            }
        });
        // checkedが付いたか外されたか
        if (checkbox_element.prop('checked')) {
            let radio_value = input_window_area.find('input[type="radio"]:checked').val();
            if (radio_value == "store") {
                radio_value = "ストア";
            }
            if (radio_value) {
                update_text_field(input_name, radio_value, checkbox_element.val(), masta_number_area, 'add');
            }
        } else {
            let radio_value = input_window_area.find('input[type="radio"]:checked').val();
            if (radio_value == "store") {
                radio_value = "ストア";
            }
            if (radio_value) {
                update_text_field(input_name, radio_value, checkbox_element.val(), masta_number_area, 'remove');
            }
        }
    }

    //////////////////////////////////////////////
    // テキストフィールドの値を更新する関数(どこのテキストフィールドか,ラジオボタンの値,チェックボックスの値,どこの場所なのか,追加or削除)
    /////////////////////////////////////////////
    function update_text_field(input_name, radio_value, checkbox_value, masta_number_area, action) {
        const line_name = radio_value + checkbox_value;
        const input_element = masta_number_area.find(`input[name="${input_name}"]`);
        let existing_lines = input_element.val() ? input_element.val().split(',') : [];
        //文字列を検索して追加or削除
        if (action == 'add') {
            existing_lines.push(line_name);
        } else {
            existing_lines = existing_lines.filter(line => line !== line_name);
        }

        input_element.val(existing_lines.join(','));
    }

    ////////////////////////////////////////////////
    // ストア・W/C選択の閉じるボタンが押されたときの関数
    //////////////////////////////////////////////////
    function close_button(button_element) {
        let input_window_area = button_element.closest('.input_window');
        input_window_area.css('display', 'none');
        // 要素を削除
        input_window_area.remove();
    }


    /////////////////////////////////////////
    // インプットウィンドウのHTMLを生成する関数
    /////////////////////////////////////////
    function create_input_window_html() {
        return `
            <div class="input_window">
                <div class="flax">
                    <div class="left_window">
                        <div class="radio_area"></div>
                    </div>
                    <div class="right_window">
                        <div class="checkbox_area"></div>
                    </div>
                </div>
                <div class="btn_areas">
                    <div id="close_btn" class="btn btn-secondary close_btn">閉じる</div>
                </div>
            </div>`;
    }
    

});
