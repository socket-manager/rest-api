<?php
/**
 * ハンドラ登録クラスのファイル
 * 
 */

namespace App\EventClass;

use App\CommandUnits\CommandForHandler;


/**
 * ハンドラ登録クラス
 * 
 * CommandForHandlerクラスをオーバーライドして利用する
 */
class <%= name %> extends CommandForHandler
{
    /**
     * JSON形式のレスポンス（サンプル用）
     * 
     * @param $p_param コンテキストパラメータ
     */
    protected function <%= sample-method %>($p_param)
    {
        $p_param->response()->json(['message' => 'Hello API']);
    }
}
