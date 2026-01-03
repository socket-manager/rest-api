<?php
/**
 * シンプルソケットのパラレルクラスのファイル
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのクラスファイル
 */

namespace App\ParallelClass;

use SocketManager\Library\SimpleSocketGenerator;
use SocketManager\Library\SimpleSocketTypeEnum;
use SocketManager\Library\<%= ISimpleSocket %>;

use App\UnitParameter\ParameterForRestApi;


/**
 * シンプルソケットのパラレルクラス
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのクラス
 */
class <%= name %> implements IParallelClass
{
    private ParameterForRestApi $context;
    private SimpleSocketGenerator $generator;

    /**
     * コンストラクタ
     * 
     * @param ?array $p_conf_param REST-APIの基本パラメータ
     * @param ParameterForRestApi $p_context REST-APIのコンテキストクラスのインスタンス
     */
    public function __construct(?array $p_conf_param, $p_context)
    {
        $this->context = $p_context;
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
        $this->generator = new SimpleSocketGenerator(<%= SimpleSocketTypeEnum %>, null, null, 1000);
        $this->generator->setUnitParameter($this->context); // REST-APIのコンテキストクラスと連携
        $this->generator->setKeepRunning(function(?<%= ISimpleSocket %> $p_simple_socket, $p_context)
        {
            // ここにイベントループで処理する内容を書く
        }, $this->context);

        $w_ret = $this->generator->generate();
        if($w_ret === null)
        {
            return false;
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
        $ret = $this->generator->cycleDriven($p_cycle_interval);
        if($ret === false)
        {
            $this->generator->shutdownAll();
            return false;
        }

        return true;
    }
}
