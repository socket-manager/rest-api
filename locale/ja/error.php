<?php

return [

    /**
     * ラベル
     */
    'LABEL_ILLEGAL_ACCESS' => '不正アクセス',


    /**
     * メッセージ
     */

    // 内部エラー
    'MESSAGE_NO_REQUEST' => 'リクエストされないまま終了しました（:cid）。',

    // Reason指定用
    'MESSAGE_REASON_BAD_REQUEST' => 'Bad Request',
    'MESSAGE_REASON_NOT_FOUND' => 'Not Found',
    'MESSAGE_REASON_NO_CONTENT' => 'No Content',
    'MESSAGE_REASON_METHOD_NOT_ALLOWED' => 'Method Not Allowed',
    'MESSAGE_REASON_PAYLOAD_TOO_LARGE' => 'Payload Too Large',

    // ボディ部指定用
    'MESSAGE_BODY_NOT_FOUND_PARAM' => 'Not Found: :param',
    'MESSAGE_BODY_ROUTING_MISMATCH' => 'ルーティングミスマッチ'
];
