<?php
/**
 * ステータスUNIT登録クラスのファイル
 * 
 * SocketManagerのsetCommandUnitsメソッドへ引き渡されるクラスのファイル
 */

namespace App\CommandUnits;


/**
 * コマンドUNIT登録クラス
 * 
 * CommandForRestApiクラスを継承する
 */
class CommandForStateMachine extends CommandForRestApi
{
    /**
     * ステータスUNITリストの取得
     * 
     * @param string $p_que キュー名
     * @return array キュー名に対応するUNITリスト
     */
    public function getUnitList(string $p_que): array
    {
        $ret = $this->$p_que();
        return $ret;
    }
}
