<?php
/**
 * プロトコルUNITステータス名のENUMファイル
 * 
 * StatusEnumの定義を除いて自由定義
 */

namespace App\ProtocolUnits;


use SocketManager\Library\StatusEnum;


/**
 * プロトコルUNITステータス名定義
 * 
 * プロトコルUNITのステータス予約名はSTART（処理開始）のみ
 */
enum ProtocolForRestApiStatusEnum: string
{
    /**
     * @var string 処理開始時のステータス共通
     */
    case START = StatusEnum::START->value;

    /**
     * @var string ヘッダ部受信ステータス
     */
    case HEADER = 'header';

    /**
     * @var string ヘッダ部（Expect）受信ステータス
     */
    case HEADER_EXPECT = 'header_expect';

    /**
     * @var string ボディ部受信（Content-Length）ステータス
     */
    case BODY_LENGTH = 'body_length';

    /**
     * @var string ボディ部受信（multipart）ステータス
     */
    case BODY_MULTIPART = 'body_multipart';

    /**
     * @var string ボディ部受信（Chunked）ステータス
     */
    case BODY_CHUNKED = 'body_chunked';

    /**
     * @var string リクエストインスタンス生成ステータス
     */
    case BUILD_REQUEST = 'build_request';

    /**
     * @var string 送信中ステータス
     */
    case SENDING = 'sending';
}
