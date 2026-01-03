<?php
/**
 * ボディ部パーサークラスのファイル
 * 
 * 設定ファイルで指定されるパーサークラス
 */

namespace App\BodyParser;

use App\Common\RequestWrapper;


/**
 * ボディ部パーサークラス
 * 
 */
class <%= name %> extends BodyParser
{
    /**
     * パース処理メソッドの選択処理
     * 
     * @param RequestWrapper $p_request リクエストインスタンス
     * @param string $p_endpoint エンドポイントURI
     * @return ?string 処理対象メソッド名
     */
    public function selector(RequestWrapper $p_request, string $p_endpoint): ?string
    {
        return 'normal';
    }

    /**
     * デフォルトパース処理
     * 
     * @param mixed $p_body ボディ部のデータ
     * @return mixed パース後のデータ
     */
    public function normal($p_body)
    {
        return $p_body;
    }
}
