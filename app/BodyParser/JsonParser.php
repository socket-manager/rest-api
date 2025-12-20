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
 * "application/json"タイプのパーサークラス
 */
class JsonParser extends BodyParser
{
    /**
     * デフォルトパース処理
     * 
     * @param mixed $p_body ボディ部のデータ
     * @return mixed パース後のデータ
     */
    public function normal($p_body)
    {
        // JSON → 配列に変換
        return json_decode($p_body, true);
    }
}
