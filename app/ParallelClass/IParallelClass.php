<?php
/**
 * パラレルクラスのインタフェースファイル
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのインターフェースファイル
 */

namespace App\ParallelClass;


/**
 * パラレルクラスのインタフェース
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのインターフェース定義
 */
interface IParallelClass
{
    /**
     * メイン処理の初期化処理
     * 
     * 初期化処理の依存性注入
     * 
     * @return bool true（成功） or false（失敗）
     */
    public function initMain(): bool;

    /**
     * 周期ドリブン処理
     * 
     * イベントループへの依存性注入
     * 
     * @param int $p_cycle_interval REST-APIの周期インターバルタイム（マイクロ秒）
     * @param int $p_alive_interval REST-APIのアライブチェックインターバルタイム（秒）
     * @return bool true（成功） or false（失敗）
     */
    public function cycleDriven(int $p_cycle_interval, int $p_alive_interval): bool;
}
