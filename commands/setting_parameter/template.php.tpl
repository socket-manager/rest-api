<?php


return [

    /**
     * @var string ホスト名
     */
    'host' => '<%= host %>',

    /**
     * @var int ポート番号
     */
    'port' => <%= port %>,

    /**
     * @var int 同時接続制限数
     */
    'limit_connection' => <%= limit_connection %>,

    /**
     * @var int 受信バッファサイズ（バイト数）
     */
    'receive_buffer_size' => <%= receive_buffer_size %>,

    /**
     * @var int リクエスト（ヘッダ＋ボディ）制限サイズ（バイト数）。nullの場合は制限なし。
     */
    'limit_request_size' => <%= limit_request_size %>,

    /**
     * @var int ボディ部制限サイズ（バイト数）。nullの場合は制限なし。
     */
    'limit_body_size' => <%= limit_body_size %>,

    /**
     * 周期インターバル時間（μs）
     */
    'cycle_interval' => <%= cycle_interval %>,

    /**
     * @var int アライブチェックタイムアウト時間（s）
     */
    'alive_interval' => <%= alive_interval %>,

    /**
     * @var bool KeepAlive フラグ（true：ON or false：OFF）
     */
    'keep_alive' => <%= keep_alive %>,

    /**
     * @var string ETag生成時のモード ⇒ 'strong'（厳密比較） or 'weak'（緩い比較）
     */
    'etag' => '<%= etag %>'

];
