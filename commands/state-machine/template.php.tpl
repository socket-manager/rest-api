<?php
/**
 * ステートマシン登録クラスのファイル
 * 
 */

namespace App\EventClass;

use App\CommandUnits\CommandForStateMachine;


/**
 * ステートマシン登録クラス
 * 
 * CommandForStateMachineクラスをオーバーライドして利用する
 */
class <%= name %> extends CommandForStateMachine
{
    /**
     * JSON形式のレスポンス（サンプル用）
     * 
     */
    protected function <%= sample-method %>()
    {
        return [
            [
                'status' => 'start',
                'unit' => function($p_param): ?string
                {
                    $p_param->response()->json(['message' => 'Hello API']);

                    return null;
                }
            ]
        ];
    }

}
