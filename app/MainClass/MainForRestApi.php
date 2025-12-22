<?php
/**
 * メイン処理の基底クラスのファイル
 * 
 * SocketManagerの実行
 */

namespace App\MainClass;

use SocketManager\Library\SocketManager;
use SocketManager\Library\FrameWork\Console;
use App\Common\LaminasFactory;
use App\InitClass\InitForRestApi;
use App\UnitParameter\ParameterForRestApi;
use App\ProtocolUnits\ProtocolForRestApi;
use App\CommandUnits\CommandForRestApi;
use App\ParallelClass\IParallelClass;


/**
 * メイン処理の基底クラス
 * 
 * SocketManagerの初期化と実行
 */
class MainForRestApi extends Console
{
    /**
     * @var string $identifer サーバー識別子
     */
    protected string $identifer = 'base main class';

    /**
     * @var string $description コマンド説明
     */
    protected string $description = 'メイン処理の基底クラス';

    /**
     * @var array $setting_files 設定ファイル指定
     */
    protected array $setting_files = [
        'cors'      => 'cors',
        'parameter' => 'parameter',
        'parser'    => 'parser',
        'routing'   => 'routing'
    ];

    /**
     * @var array $classes 設定クラス群
     */
    protected array $classes = [
        'context'  => ParameterForRestApi::class,
        'event'    => CommandForRestApi::class,
        'parallel' => null
    ];

    /**
     * @var int $port ポート番号
     */
    protected int $port;

    /**
     * @var ?IParallelClass $parallel パラレルインターフェースのインスタンス
     */
    protected ?IParallelClass $parallel = null;


    /**
     * 設定ファイル検証
     * 
     * @param ?string $p_child 子クラスの設定ファイル名
     * @param ?string $p_parent 親クラスの設定ファイル名
     * @return array|bool|null 設定ファイル内容 or false（設定ファイルが存在しない） or null（指定なし）
     */
    protected function checkConfig(?string $p_child, ?string $p_parent): array|bool|null
    {
        $conf_parent = [];
        if($p_parent !== null)
        {
            $conf_parent = config($p_parent);
        }
        if($p_child === null || !strlen($p_child))
        {
            return $conf_parent;
        }

        $conf_child = config($p_child, null);
        if($conf_child === null)
        {
            $msg = __('system_error.MESSAGE_NO_SETTING_FILE', ['name' => $p_child]);
            printf("{$msg}\n");
            return false;
        }

        foreach($conf_parent as $key => $val)
        {
            if(!isset($conf_child[$key]))
            {
                $conf_child[$key] = $val;
            }
        }

        return $conf_child;
    }

    /**
     * クラス検証
     * 
     * @param ?string $p_child 子クラス
     * @param string $p_parent 親クラス
     * @return string|bool クラス名 or false（親クラスが一致しない）
     */
    protected function checkClass(?string $p_child, string $p_parent): string|bool
    {
        if($p_child === null || !strlen($p_child))
        {
            return $p_parent;
        }
        if($p_child === $p_parent)
        {
            return $p_child;
        }
        if(is_subclass_of($p_child, $p_parent))
        {
            return $p_child;
        }
        return false;
    }

    /**
     * サーバー起動
     * 
     */
    public function exec()
    {
        $manager = null;

        $conf_parameter = $this->checkConfig($this->setting_files['parameter'], 'parameter');
        if($conf_parameter === false)
        {
            goto finish;
        }
        $conf_cors = $this->checkConfig($this->setting_files['cors'], null);
        if($conf_cors === false)
        {
            goto finish;
        }
        $conf_parser = $this->checkConfig($this->setting_files['parser'], null);
        if($conf_parser === false)
        {
            goto finish;
        }
        $conf_routing = $this->checkConfig($this->setting_files['routing'], 'routing');
        if($conf_routing === false)
        {
            goto finish;
        }

        $class_parameter = $this->checkClass($this->classes['context'], ParameterForRestApi::class);
        if($class_parameter === false)
        {
            $msg = __('system_error.MESSAGE_MISMATCH_CLASS', ['name' => __('system_error.LABEL_PARAMETER_CLASS')]);
            printf("{$msg}\n");
            goto finish;
        }
        $class_command = $this->checkClass($this->classes['event'], CommandForRestApi::class);
        if($class_command === false)
        {
            $msg = __('system_error.MESSAGE_MISMATCH_CLASS', ['name' => __('system_error.LABEL_COMMAND_CLASS')]);
            printf("{$msg}\n");
            goto finish;
        }
        $ins_command = new $class_command();

        $queues = [];
        foreach($conf_routing['routes'] as $route)
        {
            if($route['event'] === null || $route['event'] === '')
            {
                continue;
            }
            if(!method_exists($ins_command, $route['event']))
            {
                $msg = __('system_error.MESSAGE_MISMATCH_CLASS_METHOD', ['name' => $route['event']]);
                printf("{$msg}\n");
                goto finish;
            }
            $queues[] = $route['event'];
        }
        if($conf_routing['mismatch'] !== null && $conf_routing['mismatch'] !== '')
        {
            if(!method_exists($ins_command, $conf_routing['mismatch']))
            {
                $msg = __('system_error.MESSAGE_MISMATCH_CLASS_METHOD', ['name' => $conf_routing['mismatch']]);
                printf("{$msg}\n");
                goto finish;
            }
            $queues[] = $conf_routing['mismatch'];
        }
        $ins_command->setQueueList($queues);

        // 引数の取得
        $cli_parameter = $conf_parameter;
        foreach($conf_parameter as $name => $value)
        {
            try
            {
                $arg = $this->getParameter($name);
            }
            catch(\InvalidArgumentException $e)
            {
                // 引数が存在しない場合は null を設定
                $arg = null;
            }
            $cli_parameter[$name] = $arg;
            if($cli_parameter[$name] === null || $cli_parameter[$name] === false)
            {
                $cli_parameter[$name] = $value;
            }
            switch(gettype($conf_parameter[$name]))
            {
                case 'integer':
                    $cli_parameter[$name] = (int)$cli_parameter[$name];
                    break;
                case 'double':
                    $cli_parameter[$name] = (float)$cli_parameter[$name];
                    break;
                case 'boolean':
                    // "true"/"false" を文字列から判定
                    $cli_parameter[$name] = filter_var($cli_parameter[$name], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    break;
                case 'string':
                    $cli_parameter[$name] = (string)$cli_parameter[$name];
                    break;
                case 'NULL':
                    $cli_parameter[$name] = null;
                    break;
                default:
                    $cli_parameter[$name] = $cli_parameter[$name]; // その他はそのまま
            }
        }

        $this->port = $cli_parameter['port'];

        // ソケットマネージャーのインスタンス設定
        $manager = new SocketManager($cli_parameter['host'], $cli_parameter['port'], $cli_parameter['receive_buffer_size'], $cli_parameter['limit_connection']);

        /***********************************************************************
         * ソケットマネージャーの初期設定
         * 
         * プロトコル／コマンド部等で実装したクラスのインスタンスをここで設定します
         **********************************************************************/

        /**
         * 初期化クラスの設定
         * 
         */
        $parsers = [];
        foreach($conf_parser as $type => $parser)
        {
            if($parser === null)
            {
                continue;
            }
            if($type === 'multipart/form-data')
            {
                continue;
            }
            $parsers[$type] = new $parser;
        }
        $origins = [];
        if($conf_cors !== [])
        {
            $origins = $conf_cors['allow_origins'];
        }
        $psr7_factory = new LaminasFactory();
        $unit_parameter = new $class_parameter($psr7_factory, $origins, $conf_parameter['etag']);
        $log_writer = null;
        if(is_callable([$this, 'logWriter']))
        {
            $log_writer = [$this, 'logWriter'];
        }
        $manager->setInitSocketManager(new InitForRestApi($unit_parameter, $conf_routing, $parsers, $cli_parameter['port'], $log_writer));

        /**
         * プロトコルUNITの設定
         * 
         */
        $tmp_methods = array_column($conf_routing['routes'], 'method');
        $tmp_methods = array_map('strtoupper', $tmp_methods);
        $methods = array_intersect(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $tmp_methods);
        $manager->setProtocolUnits(new ProtocolForRestApi($cli_parameter['limit_request_size'], $cli_parameter['limit_body_size'], $cli_parameter['keep_alive'], $methods, $conf_cors, $conf_routing['expect']));

        /**
         * コマンドUNITの設定
         * 
         */
        $manager->setCommandUnits($ins_command);

        /***********************************************************************
         * ソケットマネージャーの実行
         * 
         * ポートの待ち受け処理や周期ドリブン処理を実行します
         **********************************************************************/

        // リッスンポートで待ち受ける
        $ret = $manager->listen();
        if($ret === false)
        {
            goto finish;   // リッスン失敗
        }

        // パラレルクラスの初期化処理
        if($this->classes['parallel'] !== null)
        {
            $this->parallel = new $this->classes['parallel']($unit_parameter);
            $this->parallel->initMain();
        }

        $cycle_interval = $cli_parameter['cycle_interval'];
        $alive_interval = $cli_parameter['alive_interval'];

        // ノンブロッキングループ
        while(true)
        {
            // 周期ドリブン
            $ret = $manager->cycleDriven($cycle_interval, $alive_interval);
            if($ret === false)
            {
                goto finish;
            }

            // 周期ドリブン（パラレルクラス用）
            if($this->parallel)
            {
                $this->parallel->cycleDriven($cycle_interval, $alive_interval);
                if($ret === false)
                {
                    goto finish;
                }
            }
        }

finish:
        // 全接続クローズ
        if($manager)
        {
            $manager->shutdownAll();
        }
    }
}
