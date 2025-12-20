<?php
/**
 * ステータスUNIT登録クラスのファイル
 * 
 * SocketManagerのsetCommandUnitsメソッドへ引き渡されるクラスのファイル
 */

namespace App\CommandUnits;

use SocketManager\Library\IEntryUnits;


/**
 * コマンドUNIT登録クラス
 * 
 * IEntryUnitsインタフェースをインプリメントする
 */
class CommandForRestApi implements IEntryUnits
{
    /**
     * @var const QUEUE_LIST キュー名のリスト
     */
    protected array $QUEUE_LIST = [];


    /**
     * コンストラクタ
     * 
     */
    public function __construct()
    {
    }

    /**
     * キューリストの取得
     * 
     * @return array キュー名のリスト
     */
    public function getQueueList(): array
    {
        return (array)$this->QUEUE_LIST;
    }

    /**
     * キューリストの設定
     * 
     * @param array キュー名のリスト
     */
    public function setQueueList(array $p_ques): void
    {
        $this->QUEUE_LIST = $p_ques;
    }

    /**
     * ステータスUNITリストの取得
     * 
     * @param string $p_que キュー名
     * @return array キュー名に対応するUNITリスト
     */
    public function getUnitList(string $p_que): array
    {
        return [];
    }
}
