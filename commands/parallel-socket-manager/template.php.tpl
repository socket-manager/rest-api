<?php
/**
 * ソケットマネージャーのパラレルクラスのファイル
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのクラスファイル
 */

namespace App\ParallelClass;

use SocketManager\Library\SocketManager;


/**
 * ソケットマネージャーのパラレルクラス
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのクラス
 */
class <%= name %> implements IParallelClass
{
    /**
     * @var SocketManager ソケットマネージャーのインスタンス
     */
    private SocketManager $manager;

    /**
     * @var ?array 基本パラメータ
     */
    private ?array $conf_param = null;

    /**
     * コンストラクタ
     * 
     * @param ?array $p_conf_param REST-APIの基本パラメータ
     * @param ContextForSample $p_context REST-APIのコンテキストクラスのインスタンス
     */
    public function __construct(?array $p_conf_param, $p_context)
    {
        $this->conf_param = $p_conf_param;
        if($p_conf_param === null)
        {
            $this->conf_param['host'] = null;
        }
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
        // ソケットマネージャーのインスタンス設定
        $this->manager = new SocketManager($this->conf_param['host'], <%= port %>);

        /***********************************************************************
         * ソケットマネージャーの初期設定
         * 
         * プロトコル／コマンド部等で実装したクラスのインスタンスをここで設定します
         **********************************************************************/

        /**
         * 初期化クラスの設定
         * 
         * $this->manager->setInitSocketManager()メソッドで初期化クラスを設定します
         */

        /**
         * プロトコルUNITの設定
         * 
         * $this->manager->setProtocolUnits()メソッドでプロトコルUNITクラスを設定します
         */

        /**
         * コマンドUNITの設定
         * 
         * $this->manager->setCommandUnits()メソッドでコマンドUNITクラスを設定します
         */

        /***********************************************************************
         * ソケットマネージャーの実行
         * 
         * ポートの待ち受け処理や周期ドリブン処理を実行します
         **********************************************************************/

        // リッスンポートで待ち受ける
        $ret = $this->manager->listen();
        if($ret === false)
        {
            return false;   // リッスン失敗
        }

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
        $ret = $this->manager->cycleDriven($p_cycle_interval, $p_alive_interval);
        if($ret === false)
        {
            $this->manager->shutdownAll();
            return false;
        }

        return true;
    }
}
