<?php
/**
 * ボディ部パーサークラスのファイル
 * 
 * 設定ファイルで指定されるパーサークラス
 */

namespace App\BodyParser;


/**
 * ボディ部パーサークラス
 * 
 * "application/xml"タイプのパーサークラス
 */
class XmlParser extends BodyParser
{
    /**
     * デフォルトパース処理
     * 
     * @param mixed $p_body ボディ部のデータ
     * @return mixed パース後のデータ
     */
    public function normal($p_body)
    {
        // XML → SimpleXMLでパースして配列化
        $xml = simplexml_load_string($p_body);
        return json_decode(json_encode($xml), true);
    }
}
