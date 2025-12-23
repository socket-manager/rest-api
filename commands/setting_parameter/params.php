<?php

return [
    /**
     * @var string ホスト名
     */
    'host' => 'localhost',

    /**
     * @var int ポート番号
     */
    'port' => '10000',

    /**
     * @var int 同時接続制限数
     */
    'limit_connection' => '100',

    /**
     * @var int 受信バッファサイズ（バイト数）
     */
    'receive_buffer_size' => '1024',

    /**
     * @var int リクエスト（ヘッダ＋ボディ）制限サイズ（バイト数）。nullの場合は制限なし。
     */
    'limit_request_size' => '12582912',

    /**
     * @var int ボディ部制限サイズ（バイト数）。nullの場合は制限なし。
     */
    'limit_body_size' => '10485760',

    /**
     * 周期インターバル時間（μs）
     */
    'cycle_interval' => '10',

    /**
     * @var int アライブチェックタイムアウト時間（s）
     */
    'alive_interval' => '5',

    /**
     * @var bool KeepAlive フラグ（true：ON or false：OFF）
     */
    'keep_alive' => 'false',

    /**
     * @var string ETag生成時のモード ⇒ 'strong'（厳密比較） or 'weak'（緩い比較）
     */
    'etag' => 'strong'
];
