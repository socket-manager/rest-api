<?php


return [

    /**
     * @var array ルーティング定義リスト
     */
    'routes' =>
    [
        /**
         * usersのグループ
         */
        [
            'uri' => '/api/v1/users',
            'group' => [
                /**
                 * @var array ユーザー情報の取得
                 */
                [
                    'method' => 'get',
                    'uri' => '/:id([0-9]+)?',
                    'event' => 'getUsers'
                ],

                /**
                 * @var array 新規ユーザー作成
                 */
                [
                    'method' => 'post',
                    'uri' => '',
                    'event' => 'postUser'
                ],

                /**
                 * @var array ユーザー全体更新
                 */
                [
                    'method' => 'put',
                    'uri' => '/:id([0-9]+)',
                    'event' => 'putUser'
                ],

                /**
                 * @var array ユーザー部分更新
                 */
                [
                    'method' => 'patch',
                    'uri' => ':/(?P<id>[0-9]+)$:',
                    'event' => 'patchUser'
                ],

                /**
                 * @var array ユーザー削除
                 */
                [
                    'method' => 'delete',
                    'uri' => '/:id([0-9]+)',
                    'event' => 'deleteUser'
                ]
            ]
        ],

        /**
         * multipartのグループ
         */
        [
            'uri' => '/api/v1/multipart',
            'group' => [
                /**
                 * @var array ファイルアップロード（multipart）
                 */
                [
                    'method' => 'post',
                    'uri' => '/upload',
                    'event' => 'fileUploadByMultipart'
                ],

                /**
                 * @var array アップロードファイル情報の取得（multipart）
                 */
                [
                    'method' => 'get',
                    'uri' => '/files',
                    'event' => 'getFilesByMultipart'
                ],

                /**
                 * @var array アップロードファイルの取得（multipart）
                 */
                [
                    'method' => 'get',
                    'uri' => '/files/:id([0-9]+)/image',
                    'event' => 'getFileByMultipart'
                ],

                /**
                 * @var array ファイルダウンロード（multipart）
                 */
                [
                    'method' => 'get',
                    'uri' => '/files/:id([0-9]+)/download',
                    'event' => 'downloadFileByMultipart'
                ]
            ]
        ],

        /**
         * chunkedのグループ
         */
        [
            'uri' => '/api/v1/chunked',
            'group' => [
                /**
                 * @var array ファイルアップロード（chunked）
                 */
                [
                    'method' => 'post',
                    'uri' => '/upload',
                    'event' => 'fileUploadByChunked'
                ],

                /**
                 * @var array アップロードファイル情報の取得（chunked）
                 */
                [
                    'method' => 'get',
                    'uri' => '/files',
                    'event' => 'getFilesByChunked'
                ],

                /**
                 * @var array アップロードファイルの取得（chunked）
                 */
                [
                    'method' => 'get',
                    'uri' => '/files/:id([0-9]+)/image',
                    'event' => 'getFileByChunked'
                ],

                /**
                 * @var array ファイルダウンロード（chunked）
                 */
                [
                    'method' => 'get',
                    'uri' => '/files/:id([0-9]+)/download',
                    'event' => 'downloadFileByChunked'
                ],

                /**
                 * @var array チャンク転送ストリーム（chunked）
                 */
                [
                    'method' => 'get',
                    'uri' => '/stream',
                    'event' => 'getStreamByChunked'
                ]
            ]
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
         * rangeのグループ
         */
        [
            'uri' => '/api/v1/range',
            'group' => [
                /**
                 * @var array Range指定のデータ取得（バイナリ形式）
                 */
                [
                    'method' => 'get',
                    'uri' => '/binary',
                    'event' => 'getBinaryByRange'
                ],

                /**
                 * @var array Range指定のデータ取得（ファイル形式）
                 */
                [
                    'method' => 'get',
                    'uri' => '/file',
                    'event' => 'getFileByRange'
                ]
            ]
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
