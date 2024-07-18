
$(function()
{
    let factory = $('[name="factory"]');
    let department = $('[name="department"]');
    let line = $('[name="line"]');
    let numbers = $('[name="numbers"]');
    let workers = $('[name="workers"]');

    /////////////////////////////////////////////////
    // selectが変更されたときの処理
    ///////////////////////////////////////////////
    $('select').change(function() {
        let select_id = $(this).attr('id');
        let select_val = $(this).val();
        switch (select_id) {
            //工場が選択されたとき
            case "factory":
                factory.val(select_val)
                factory_change(select_val);
                option_reset("department");
                break;
            //製造課が選択されたとき
            case "department":
                department.val(select_val)
                option_reset("workers");
                option_reset("line");
                workers_get(select_val)
                line_get(select_val)
                break;
             //ラインが選択されたとき
            case "line":
                line.val(select_val)
                option_reset("numbers");
                numbers_get(select_val)
                break;
            //設備番号が選択されたとき
            case "numbers":
                numbers.val(select_val)
                break;
            //作業者が選択されたとき
            case "workers":
                workers.val(select_val)
                break;
            default:
                break;
        }

    });

    function factory_change(factory_id)
    {
        department.val($('#department option:first').val());
        line.val($('#department option:first').val());
        numbers.val($('#department option:first').val());
        workers.val($('#department option:first').val());
        $.ajax({
            url: "../ajax/longinfos/department_get.php",
            type: "POST",
            dataType: "json",
            data: {
                factory_id: factory_id,
            },
        })
        .done(function (data) {
            data.forEach((element) => {
                // optionタグを作成する
                var option = $("<option></option>");
                option.text(element["name"]);
                option.val(element["id"]);
                department.append(option);
            });
        })
        .fail(function (XMLHttpRequest, textStatus, errorThrown) {
            window.alert(
                "DB接続に失敗しました。\nシステム担当にご連絡ください。"
            );
            console.log("作業者:Ajax,失敗");
            console.log("XMLHttpRequest : " + XMLHttpRequest);
            console.log("textStatus : " + textStatus);
            console.log("errorThrown : " + errorThrown);
        });
    }

    function workers_get(select_val)
    {
        line.val($('#department option:first').val());
        numbers.val($('#department option:first').val());
        workers.val($('#department option:first').val());
        $.ajax({
            url: "../ajax/longinfos/woker_get.php",
            type: "POST",
            dataType: "json",
            data: {
                factory_id: factory.val(),
                department_id: select_val
            },
        })
        .done(function (data) {
            data.forEach((element) => {
                // optionタグを作成する
                var option = $("<option></option>");
                option.text(element["name"]);
                option.val(element["id"]);
                workers.append(option);
            });
        })
        .fail(function (XMLHttpRequest, textStatus, errorThrown) {
            window.alert(
                "DB接続に失敗しました。\nシステム担当にご連絡ください。"
            );
            console.log("作業者:Ajax,失敗");
            console.log("XMLHttpRequest : " + XMLHttpRequest);
            console.log("textStatus : " + textStatus);
            console.log("errorThrown : " + errorThrown);
        });
    }

    function line_get(select_val)
    {
        numbers.val($('#department option:first').val());
        $.ajax({
            url: "../ajax/longinfos/line_get.php",
            type: "POST",
            dataType: "json",
            data: {
                factory_id: factory.val(),
                department_id: select_val
            },
        })
        .done(function (data) {
            data.forEach((element) => {
                var option = $("<option></option>");
                option.text(element["line"]);
                option.val(element["line"]);
                line.append(option);
            });
            // optionタグを作成する
            var option = $("<option></option>");
            option.text("ストア");
            option.val("store");
            line.append(option);
        })
        .fail(function (XMLHttpRequest, textStatus, errorThrown) {
            window.alert(
                "DB接続に失敗しました。\nシステム担当にご連絡ください。"
            );
            console.log("作業者:Ajax,失敗");
            console.log("XMLHttpRequest : " + XMLHttpRequest);
            console.log("textStatus : " + textStatus);
            console.log("errorThrown : " + errorThrown);
        });
    }

    function numbers_get(select_val)
    {
        let url;
        if(select_val == "store")
        {
            url = "../ajax/longinfos/store_get.php";
        }   
        else
        {
            url = "../ajax/longinfos/numbers_get.php";
        }
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: {
                factory_id: factory.val(),
                department_id: department.val(),
                line: select_val

            },
        })
        .done(function (data) {
            data.forEach((element) => {
                if(select_val == "store")
                {
                    var option = $("<option></option>");
                    option.text(element["store"]);
                    option.val(element["store"]);
                    numbers.append(option);
                }
                else
                {
                    var option = $("<option></option>");
                    option.text(element["equipment_id"]);
                    option.val(element["equipment_id"]);
                    numbers.append(option);
                }
            });
        })
        .fail(function (XMLHttpRequest, textStatus, errorThrown) {
            window.alert(
                "DB接続に失敗しました。\nシステム担当にご連絡ください。"
            );
            console.log("作業者:Ajax,失敗");
            console.log("XMLHttpRequest : " + XMLHttpRequest);
            console.log("textStatus : " + textStatus);
            console.log("errorThrown : " + errorThrown);
        });
    }

    function option_reset(select_id)
    {   
        const $selectElement = $("#" + select_id);
        if ($selectElement.length) {
        // option要素をすべて取得
        $selectElement.find("option:not(:first)").remove();
        }
    }

});