<?php
/**
 * ボディ部パーサー基底クラスのファイル
 * 
 * 設定ファイルで指定されたパーサークラスの基底クラス
 */

namespace App\BodyParser;

use App\Common\RequestWrapper;

/**
 * ボディ部パーサー基底クラス
 * 
 * Content-Typeごとのパーサークラスの基底クラス
 */
class BodyParser
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
     * パース処理（デフォルト）
     * 
     * @param mixed $p_body ボディ部のデータ
     * @return mixed パース後のデータ
     */
    public function normal($p_body)
    {
        return $p_body;
    }
}
