<?php
/**
 * パラレルクラスのファイル
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのクラスファイル
 */

namespace App\ParallelClass;


/**
 * パラレルクラス
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのクラス
 */
class <%= name %> implements IParallelClass
{
    /**
     * コンストラクタ
     * 
     * @param $p_context コンテキストクラスのインスタンス
     */
    public function __construct($p_context)
    {
    }

    /**
     * メイン処理の初期化処理
     * 
     * 初期化処理の依存性注入
     * 
     * @return bool true（成功） or false（失敗）
     */
    public function initMain(): bool
    {
        /**
         * ここにメイン処理の初期化処理を実装します
         */

        return true;
    }

    /**
     * 周期ドリブン処理
     * 
     * イベントループへの依存性注入
     * 
     * @param int $p_cycle_interval REST-APIの周期インターバルタイム（マイクロ秒）
     * @param int $p_alive_interval REST-APIのアライブチェックインターバルタイム（秒）
     * @return bool true（成功） or false（失敗）
     */
    public function cycleDriven(int $p_cycle_interval, int $p_alive_interval): bool
    {
        /**
         * ここにイベントループ内の処理を実装します
         */

        return true;
    }
}
