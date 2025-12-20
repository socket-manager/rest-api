<?php
/**
 * メイン処理クラスのファイル
 * 
 * SocketManagerの実行
 */

namespace App\MainClass;

use App\ContextClass\ContextForSample;
use App\EventClass\EventHandlerSample;


/**
 * メイン処理クラス
 * 
 * メイン処理の初期化と実行
 */
class MainForEventHandlerSample extends MainForRestApi
{
    /**
     * @var string $identifer サーバー識別子
     */
    protected string $identifer = 'app:event-handler-sample {port?} {keep_alive?}';

    /**
     * @var string $description コマンド説明
     */
    protected string $description = 'イベントハンドラタイプのサンプルサーバー';

    /**
     * @var array $setting_files 設定ファイル指定
     */
    protected array $setting_files = [
        'cors'      => 'cors-sample',
        'parameter' => 'parameter-sample',
        'parser'    => 'parser-sample',
        'routing'   => 'routing-sample'
    ];

    /**
     * @var array $classes 設定クラス群
     */
    protected array $classes = [
        'context' => ContextForSample::class,
        'event'   => EventHandlerSample::class
    ];

    /**
     * @var \Closure|string|null $log_writer ログライター
     */
    public function logWriter(string $p_level, array $p_param)
    {
        $filename = date('Ymd');
        $now = date('Y-m-d H:i:s');
        $log = $now." {$p_level} ".print_r($p_param, true)."\n";
        error_log($log, 3, "./logs/sample/{$filename}_P{$this->port}.log");
    }
}
