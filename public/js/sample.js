$(function()
{
    const base_url = "http://localhost:10000";
    const app_version = "api/v1";

    // GET
    $("#getForm").submit(function(e)
    {
        e.preventDefault();
        let id = $("#getUserId").val();
        let url = id ? `${base_url}/${app_version}/users/${id}` : `${base_url}/${app_version}/users`;
        $.ajax({
            url: url,
            type: "GET",
            success: function(res)
            {
                $("#getResponse").text(JSON.stringify(res, null, 2));
            },
            error: function(err)
            {
                $("#getResponse").text("Error: " + JSON.stringify(err, null, 2));
            }
        });
    });

    // POST
    $("#postForm").submit(function(e)
    {
        e.preventDefault();
        let data = {
            name: $("#postName").val(),
            email: $("#postEmail").val()
        };
        $.ajax({
            url: `${base_url}/${app_version}/users`,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(data),
            success: function(res)
            {
                $("#postResponse").text(JSON.stringify(res, null, 2));
            },
            error: function(err)
            {
                $("#postResponse").text("Error: " + JSON.stringify(err, null, 2));
            }
        });
    });

    // PUT
    $("#putForm").submit(function(e)
    {
        e.preventDefault();
        let id = $("#putId").val();
        let data = {
            name: $("#putName").val(),
            email: $("#putEmail").val()
        };
        $.ajax({
            url: `${base_url}/${app_version}/users/${id}`,
            type: "PUT",
            contentType: "application/json",
            data: JSON.stringify(data),
            success: function(res)
            {
                $("#putResponse").text(JSON.stringify(res, null, 2));
            },
            error: function(err)
            {
                $("#putResponse").text("Error: " + JSON.stringify(err, null, 2));
            }
        });
    });

    // PATCH
    $("#patchForm").submit(function(e)
    {
        e.preventDefault();
        let id = $("#patchId").val();
        let data = {};
        if ($("#patchName").val()) data.name = $("#patchName").val();
        if ($("#patchEmail").val()) data.email = $("#patchEmail").val();
        $.ajax({
            url: `${base_url}/${app_version}/users/${id}`,
            type: "PATCH",
            contentType: "application/json",
            data: JSON.stringify(data),
            success: function(res)
            {
                $("#patchResponse").text(JSON.stringify(res, null, 2));
            },
            error: function(err)
            {
                $("#patchResponse").text("Error: " + JSON.stringify(err, null, 2));
            }
        });
    });

    // DELETE
    $("#deleteForm").submit(function(e)
    {
        e.preventDefault();
        let id = $("#deleteId").val();
        $.ajax({
            url: `${base_url}/${app_version}/users/${id}`,
            type: "DELETE",
            success: function(res, status, xhr)
            {
                $("#deleteResponse").text("Status: " + xhr.status + " " + xhr.statusText);
            },
            error: function(err)
            {
                $("#deleteResponse").text("Error: " + JSON.stringify(err, null, 2));
            }
        });
    });

    // MULTIPARTによるアップロード済みファイルの一覧取得
    $("#listFilesBtnForMultipart").click(function()
    {
        const url = `${base_url}/${app_version}/multipart/files`;

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function(res)
            {
                const $list = $("#filesListForMultipart");
                $list.empty(); // 一覧をクリア

                res.forEach(file => {

                    // MIMEタイプが image/ で始まるかどうかを判定
                    const is_image = file.mime && file.mime.startsWith("image/");

                    // 画像なら img タグ、画像以外ならダウンロードリンク
                    const file_preview = is_image
                        ? `<img src="${base_url}/${app_version}/multipart/files/${file.id}/image"
                            alt="${file.filename}" class="file-thumb" />`
                        : `<a href="${base_url}/${app_version}/multipart/files/${file.id}/download"
                            class="file-download" target="_blank">ダウンロード</a>`;

                    const file_item = `
                        <div class="file-item">
                            ${file_preview}
                            <ul class="file-meta">
                                <li><strong>ファイル名:</strong> ${file.filename}</li>
                                <li><strong>説明:</strong> ${file.description}</li>
                                <li><strong>MIME:</strong> ${file.mime}</li>
                                <li><strong>サイズ:</strong> ${file.size} bytes</li>
                                <li><strong>ID:</strong> ${file.id}</li>
                            </ul>
                        </div>
                    `;
                    $list.append(file_item);
                });
            },
            error: function(err)
            {
                $("#filesListForMultipart").text("Error: " + JSON.stringify(err, null, 2));
            }
        });
    });

    // MULTIPART (ファイルアップロード)
    $("#multipartForm").submit(function(e)
    {
        e.preventDefault();
        let file = $("#multipartFile")[0].files[0];
        if(!file)
        {
            $("#multipartResponse").text("ファイルを選択してください。");
            return;
        }
        let desc = $("#multipartDesc").val();
        let url = `${base_url}/${app_version}/multipart/upload`

        let fd = new FormData();
        fd.append("file", file);
        fd.append("description", desc);

        let xhr = new XMLHttpRequest();
        xhr.open("POST", url, true);

        xhr.upload.onprogress = function(ev)
        {
            if(ev.lengthComputable)
            {
                let pct = Math.round((ev.loaded / ev.total) * 100);
                $("#multipartProgress").val(pct);
            }
        };

        xhr.onload = function()
        {
            if(xhr.status >= 200 && xhr.status < 300)
            {
                $("#multipartResponse").text(xhr.responseText);
            }
            else
            {
                $("#multipartResponse").text("Error: " + xhr.status + " " + xhr.statusText + " - " + xhr.responseText);
            }
        };

        xhr.onerror = function()
        {
            $("#multipartResponse").text("Network error during upload.");
        };

        xhr.send(fd);
    });

    // CHUNKEDによるアップロード済みファイルの一覧取得
    $("#listFilesBtnForChunked").click(function()
    {
        const url = `${base_url}/${app_version}/chunked/files`;

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function(res)
            {
                const $list = $("#filesListForChunked");
                $list.empty(); // 一覧をクリア

                res.forEach(file => {

                    // MIMEタイプが image/ で始まるかどうかを判定
                    const is_image = file.mime && file.mime.startsWith("image/");

                    // 画像なら img タグ、画像以外ならダウンロードリンク
                    const file_preview = is_image
                        ? `<img src="${base_url}/${app_version}/chunked/files/${file.id}/image"
                            alt="${file.filename}" class="file-thumb" />`
                        : `<a href="${base_url}/${app_version}/chunked/files/${file.id}/download"
                            class="file-download" target="_blank">ダウンロード</a>`;

                    const file_item = `
                        <div class="file-item">
                            ${file_preview}
                            <ul class="file-meta">
                                <li><strong>ファイル名:</strong> ${file.filename}</li>
                                <li><strong>MIME:</strong> ${file.mime}</li>
                                <li><strong>サイズ:</strong> ${file.size} bytes</li>
                                <li><strong>ID:</strong> ${file.id}</li>
                            </ul>
                        </div>
                    `;
                    $list.append(file_item);
                });
            },
            error: function(err)
            {
                $("#filesListForChunked").text("Error: " + JSON.stringify(err, null, 2));
            }
        });
    });

    // CHUNKEDによるデータ受信
    $("#startReceiveBtnForChunked").click(async function()
    {
        const response = await fetch(`${base_url}/${app_version}/chunked/stream`);
        const reader = response.body.getReader();
        const decoder = new TextDecoder("utf-8");
        const output = document.getElementById("chunkedReceiveOutput");

        while(true)
        {
            const {done, value} = await reader.read();
            if(done)
            {
                break;
            }
            output.textContent += decoder.decode(value, {stream: true});
            output.textContent += `\n`;
        }
    });

    // SSEによるデータ受信
    let es = null;
    $("#startReceiveBtnForSse").click(function()
    {
        const output = document.getElementById("sseReceiveOutput");

        // すでに接続中なら一度閉じる
        if(es)
        {
            es.close();
        }

        es = new EventSource(`${base_url}/${app_version}/sse/stream`);

        es.onmessage = function(event)
        {
            output.textContent += `[${event.lastEventId}] ${event.data}\n`;
        };

        // サーバー側が "event: end" を送ったとき
        es.addEventListener("end", function(event)
        {
            output.textContent += `--- サーバーが送信完了を通知しました ---\n`;
            es.close();
        });

        // エラー時（サーバー終了後にも発火する）
        es.onerror = function()
        {
            // すでに end イベントで close 済みなら何もしない
            if(es.readyState === EventSource.CLOSED)
            {
                return;
            }

            output.textContent += "[接続エラー → 自動再接続待ち]\n";
        };
    });

    // Range指定によるデータ受信（バイナリ形式）
    $("#rangeRecvBtnForBinary").click(function()
    {
        const range_value = $("#rangeInputForBinary").val();

        $.ajax({
            url: `${base_url}/${app_version}/range/binary`,
            method: "GET",
            headers: {
                "Range": range_value
            },
            xhrFields: {
                responseType: "text"
            },
            success: function(data, status, xhr)
            {
                $("#rangeResultForBinary").text(
                    "ステータス: " + xhr.status + "\n" +
                    "Content-Range: " + xhr.getResponseHeader("Content-Range") + "\n\n" +
                    data
                );
            },
            error: function(xhr)
            {
                $("#rangeResultForBinary").text("エラー: " + xhr.status);
            }
        });
    });

    // Range指定によるデータ受信（ファイル形式）
    $("#rangeRecvBtnForFile").click(function()
    {
        const range_value = $("#rangeInputForFile").val();

        $.ajax({
            url: `${base_url}/${app_version}/range/file`,
            method: "GET",
            headers: {
                "Range": range_value
            },
            xhrFields: {
                responseType: "text"
            },
            success: function(data, status, xhr)
            {
                $("#rangeResultForFile").text(
                    "ステータス: " + xhr.status + "\n" +
                    "Content-Range: " + xhr.getResponseHeader("Content-Range") + "\n\n" +
                    data
                );
            },
            error: function(xhr)
            {
                $("#rangeResultForFile").text("エラー: " + xhr.status);
            }
        });
    });
});
