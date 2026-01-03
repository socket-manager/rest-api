<?php
/**
 * ランタイムマネージャーのパラレルクラスのファイル
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのクラスファイル
 */

namespace App\ParallelClass;

use SocketManager\Library\RuntimeManager;


/**
 * ランタイムマネージャーのパラレルクラス
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのクラス
 */
class <%= name %> implements IParallelClass
{
    /**
     * @var RuntimeManager ランタイムマネージャーのインスタンス
     */
    private RuntimeManager $manager;

    /**
     * コンストラクタ
     * 
     * @param ?array $p_conf_param REST-APIの基本パラメータ
     * @param ParameterForRestApi $p_context REST-APIのコンテキストクラスのインスタンス
     */
    public function __construct(?array $p_conf_param, $p_context)
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
        // ランタイムマネージャーのインスタンス設定
        $this->manager = new RuntimeManager();

        /***********************************************************************
         * ランタイムマネージャーの初期設定
         * 
         * ランタイムUNITクラス等のインスタンスをここで設定します
         **********************************************************************/

        /**
         * 初期化クラスの設定
         * 
         * $this->manager->setInitRuntimeManager()メソッドで初期化クラスを設定します
         */

        /**
         * ランタイムUNITの設定
         * 
         * $this->manager->setRuntimeUnits()メソッドでランタイムUNITクラスを設定します
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
        // 周期ドリブン
        $ret = $this->manager->cycleDriven($p_cycle_interval);
        if($ret === false)
        {
            return false;
        }

        return true;
    }
}
