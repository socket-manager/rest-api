<?php
/**
 * シンプルソケットのパラレルクラスのファイル
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのクラスファイル
 */

namespace App\ParallelClass;

use SocketManager\Library\SimpleSocketGenerator;
use SocketManager\Library\SimpleSocketTypeEnum;
use SocketManager\Library\ISimpleSocketUdp;

use App\ContextClass\ContextForSample;


/**
 * シンプルソケットのパラレルクラス
 * 
 * REST-APIサーバーのメイン処理クラスへ渡すためのクラス
 */
class ParallelForSimpleSocket implements IParallelClass
{
    private ContextForSample $context;
    private SimpleSocketGenerator $generator;

    /**
     * コンストラクタ
     * 
     * @param ?array $p_conf_param 基本パラメータ
     * @param ContextForSample $p_context コンテキストクラスのインスタンス
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
        $this->generator = new SimpleSocketGenerator(SimpleSocketTypeEnum::UDP, null, null, 1000);
        $this->generator->setKeepRunning(function(?ISimpleSocketUdp $p_simple_socket, ContextForSample $p_context)
        {
            $cids = $p_context->getConnectionIdAll();
            $all_cnt = count($cids);
            $send_data =
            [
                [
                    'service' => 'rest-api-sample',
                    'type' => 'user-cnt',
                    'data' => $all_cnt
                ]
            ];
            $serialize_data = json_encode($send_data);
            $w_ret = $p_simple_socket->sendto('localhost', 15000, $serialize_data);
            if($w_ret === null)
            {
                return;
            }
            else
            if($w_ret === false)
            {
                return;
            }
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
