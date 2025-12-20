<?php
/**
 * ボディ部パーサークラスのファイル
 * 
 * 設定ファイルで指定されたパーサークラス
 */

namespace App\BodyParser;


/**
 * ボディ部パーサークラス
 * 
 * "application/x-www-form-urlencoded"タイプのパーサークラス
 */
class UrlencodedParser extends BodyParser
{
    /**
     * デフォルトパース処理
     * 
     * @param mixed $p_body ボディ部のデータ
     * @return mixed パース後のデータ
     */
    public function normal($p_body)
    {
        // URLエンコード → parse_strで配列化
        parse_str($p_body, $parsed_body);
        return $parsed_body;
    }
}
