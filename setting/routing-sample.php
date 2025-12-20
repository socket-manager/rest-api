<?php


return [

    /**
     * @var array ルーティング定義リスト
     */
    'routes' =>
    [
        /**
         * @var array ユーザー情報の取得
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/users/:id([0-9]+)?',
            'event' => 'getUsers'
        ],

        /**
         * @var array 新規ユーザー作成
         */
        [
            'method' => 'post',
            'uri' => '/api/v1/users',
            'event' => 'postUser'
        ],

        /**
         * @var array ユーザー全体更新
         */
        [
            'method' => 'put',
            'uri' => '/api/v1/users/:id([0-9]+)',
            'event' => 'putUser'
        ],

        /**
         * @var array ユーザー部分更新
         */
        [
            'method' => 'patch',
            'uri' => ':/api/v1/users/(?P<id>[0-9]+)$:',
            'event' => 'patchUser'
        ],

        /**
         * @var array ユーザー削除
         */
        [
            'method' => 'delete',
            'uri' => '/api/v1/users/:id([0-9]+)',
            'event' => 'deleteUser'
        ],

        /**
         * @var array ICOファイルの取得
         */
        [
            'method' => 'get',
            'uri' => '/:file([0-9a-zA-Z_-]+\.ico$)',
            'event' => 'getIco'
        ],

        /**
         * @var array HTMLファイルの取得
         */
        [
            'method' => 'get',
            'uri' => ':.+\.html$:',
            'event' => 'getHtml'
        ],

        /**
         * @var array Javascriptファイルの取得
         */
        [
            'method' => 'get',
            'uri' => '/js/:file',
            'event' => 'getJavascript'
        ],

        /**
         * @var array CSSファイルの取得
         */
        [
            'method' => 'get',
            'uri' => '/css/:file',
            'event' => 'getCss'
        ],

        /**
         * @var array ファイルアップロード（multipart）
         */
        [
            'method' => 'post',
            'uri' => '/api/v1/multipart/upload',
            'event' => 'fileUploadByMultipart'
        ],

        /**
         * @var array アップロードファイル情報の取得（multipart）
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/multipart/files',
            'event' => 'getFilesByMultipart'
        ],

        /**
         * @var array アップロードファイルの取得（multipart）
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/multipart/files/:id([0-9]+)/image',
            'event' => 'getFileByMultipart'
        ],

        /**
         * @var array ファイルダウンロード（multipart）
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/multipart/files/:id([0-9]+)/download',
            'event' => 'downloadFileByMultipart'
        ],

        /**
         * @var array ファイルアップロード（chunked）
         */
        [
            'method' => 'post',
            'uri' => '/api/v1/chunked/upload',
            'event' => 'fileUploadByChunked'
        ],

        /**
         * @var array アップロードファイル情報の取得（chunked）
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/chunked/files',
            'event' => 'getFilesByChunked'
        ],

        /**
         * @var array アップロードファイルの取得（chunked）
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/chunked/files/:id([0-9]+)/image',
            'event' => 'getFileByChunked'
        ],

        /**
         * @var array ファイルダウンロード（chunked）
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/chunked/files/:id([0-9]+)/download',
            'event' => 'downloadFileByChunked'
        ],

        /**
         * @var array チャンク転送ストリーム（chunked）
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/chunked/stream',
            'event' => 'getStreamByChunked'
        ],

        /**
         * @var array SSE転送ストリーム
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/sse/stream',
            'event' => 'getStreamBySse'
        ],

        /**
         * @var array Range指定のデータ取得（バイナリ形式）
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/range/binary',
            'event' => 'getBinaryByRange'
        ],

        /**
         * @var array Range指定のデータ取得（ファイル形式）
         */
        [
            'method' => 'get',
            'uri' => '/api/v1/range/file',
            'event' => 'getFileByRange'
        ]

    ],

    /**
     * @var ?string Expectヘッダ受信時のルーティング先
     */
    'expect' => 'expectHeader',

    /**
     * @var ?string ルーティングミスマッチ時のエラールーティング先
     */
    'mismatch' => null
];
