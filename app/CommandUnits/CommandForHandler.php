<?php
/**
 * ステータスUNIT登録クラスのファイル
 * 
 * SocketManagerのsetCommandUnitsメソッドへ引き渡されるクラスのファイル
 */

namespace App\CommandUnits;

use SocketManager\Library\StatusEnum;

use App\UnitParameter\ParameterForRestApi;


/**
 * コマンドUNIT登録クラス
 * 
 * CommandForRestApiクラスを継承する
 */
class CommandForHandler extends CommandForRestApi
{
    /**
     * ステータスUNITリストの取得
     * 
     * @param string $p_que キュー名
     * @return array キュー名に対応するUNITリスト
     */
    public function getUnitList(string $p_que): array
    {
        $ret[] = [
            'status' => StatusEnum::START->value,
            'unit' => function(ParameterForRestApi $p_param) use ($p_que)
            {
                $this->$p_que($p_param);
            }
        ];
        return $ret;
    }
}
