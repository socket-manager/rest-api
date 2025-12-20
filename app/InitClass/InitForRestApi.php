<?php
/**
 * SocketManager初期化クラスのファイル
 * 
 * SocketManagerのsetInitSocketManagerメソッドへ引き渡される初期化クラスのファイル
 */

namespace App\InitClass;

use Psr\Http\Message\ServerRequestInterface;
use SocketManager\Library\IInitSocketManager;
use SocketManager\Library\SocketManagerParameter;

use App\UnitParameter\ParameterForRestApi;


/**
 * SocketManager初期化クラス
 * 
 * IInitSocketManagerインタフェースをインプリメントする
 */
class InitForRestApi implements IInitSocketManager
{
    //--------------------------------------------------------------------------
    // プロパティ
    //--------------------------------------------------------------------------

    /**
     * UNITパラメータクラスのインスタンス
     */
    private ParameterForRestApi $unit_parameter;

    /**
     * ルーティング定義リスト
     */
    protected array $routing = [];

    /**
     * パーサークラスリスト
     */
    protected array $parsers = [];

    /**
     * ポート番号
     */
    protected ?int $port;

    /**
     * ログライター
     */
    protected $log_writer;

    /**
     * PSR-7準拠リクエストインスタンス
     */
    private ServerRequestInterface $request;


    //--------------------------------------------------------------------------
    // メソッド
    //--------------------------------------------------------------------------

    /**
     * コンストラクタ
     * 
     * @param ParameterForRestApi $p_unit_parameter UNITパラメータ
     * @param array $p_routing ルーティング定義
     * @param array $p_parsers パーサークラスリスト
     * @param ?int $p_port ポート番号
     * @param ?callable $p_log_writer ログライター
     */
    public function __construct(ParameterForRestApi $p_unit_parameter, array $p_routing = [], array $p_parsers, ?int $p_port = null, $p_log_writer)
    {
        $this->unit_parameter = $p_unit_parameter;
        $this->routing = $p_routing;
        if($p_routing === [])
        {
            $this->routing['routes'] = [];
            $this->routing['mismatch'] = null;
        }
        $this->parsers = $p_parsers;
        $this->port = $p_port;
        $this->log_writer = $p_log_writer;
    }

    /**
     * ログライターの取得
     * 
     * nullを返す場合は無効化（但し、ライブラリ内部で出力されているエラーメッセージも出力されない）
     * 
     * @return mixed "function(string $p_level, array $p_param): void" or null（ログ出力なし）
     */
    public function getLogWriter()
    {
        return $this->log_writer;
    }

    /**
     * シリアライザーの取得
     * 
     * nullを返す場合は無効化となる。
     * エラー発生時はUnitExceptionクラスで例外をスローして切断する。
     * 
     * @return mixed "function(mixed $p_data): mixed" or null（変更なし）
     */
    public function getSerializer()
    {
        return null;
    }

    /**
     * アンシリアライザーの取得
     * 
     * nullを返す場合は無効化となる。
     * エラー発生時はUnitExceptionクラスで例外をスローして切断する。
     * 
     * @return mixed "function(mixed $p_data): mixed" or null（変更なし）
     */
    public function getUnserializer()
    {
        return null;
    }

    /**
     * コマンドディスパッチャーの取得
     * 
     * 受信データからコマンドを解析して返す
     * 
     * コマンドUNIT実行中に受信データが溜まっていた場合でもコマンドUNITの処理が完了するまで
     * 待ってから起動されるため処理競合の調停役を兼ねる
     * 
     * nullを返す場合は無効化となる。エラー発生時はUnitExceptionクラスで例外をスローして切断する。
     * 
     * @return mixed "function(SocketManagerParameter $p_param, mixed $p_dat): ?string" or null（変更なし）
     */
    public function getCommandDispatcher()
    {
        return function(ParameterForRestApi $p_param, $p_dat)
        {
            $p_param->response(true);

            $reserve = $p_param->getTempBuff(['__command_queue']);
            if($reserve !== null && $reserve['__command_queue'] !== null)
            {
                $p_param->setTempBuff(['__command_queue' => null]);
                return $reserve['__command_queue'];
            }

            $this->request = $p_dat;

            $path = trim($this->request->getUri()->getPath(), '/');
            $path_parts = $path === '' ? [] : explode('/', $path);

            foreach($this->routing['routes'] as $route)
            {
                $method = strtolower($this->request->getMethod());
                if(strtolower($route['method']) !== $method)
                {
                    continue;
                }

                $uri = trim($route['uri'], '/');

                /**
                 * URI 全体正規表現ルート
                 */
                if(preg_match('/^:(.+):$/', $uri, $m))
                {
                    $regex = $m[1];
                    $regex = trim($regex, '/');

                    $regex = str_replace('/', '\\/', $regex);
                    if(preg_match('/^' . $regex . '$/', $path, $matches))
                    {
                        $params = [];
                        foreach($matches as $key => $value)
                        {
                            if(!is_int($key))
                            {
                                $params[$key] = $value;
                            }
                        }

                        $p_param->setTempBuff(['__req-ins' => $p_dat]);
                        $p_param->setTempBuff(['__req-params' => $params]);

                        foreach($this->parsers as $type => $parser)
                        {
                            $content_type = $this->request->getHeaderLine('Content-Type');
                            if(stripos($content_type, $type) !== false)
                            {
                                $method_name = $parser->selector($p_param->request(), $this->request->getUri()->getPath());
                                $parsed_body = $parser->$method_name($this->request->getBody()->__toString());
                                $this->request = $this->request->withParsedBody($parsed_body);
                                $p_param->setTempBuff(['__req-ins' => $this->request]);
                                break;
                            }
                        }

                        return $route['event'];
                    }

                    continue;
                }

                $pattern_parts = explode('/', $uri);

                $params = [];
                $matched = true;

                $i = 0;
                $j = 0;

                while($i < count($pattern_parts) && $j < count($path_parts))
                {
                    $part = $pattern_parts[$i];
                    $path_part = $path_parts[$j];

                    // ワイルドカード対応
                    if(str_starts_with($part, '*'))
                    {
                        $param_name = substr($part, 1);
                        $params[$param_name] = implode('/', array_slice($path_parts, $j));
                        $j = count($path_parts);
                        $i = count($pattern_parts);
                        break;
                    }
                    // 正規表現付きパラメータ対応
                    else
                    if(preg_match('/^:([a-zA-Z0-9_]+)(\((.+)\))?(\?)?$/', $part, $matches))
                    {
                        $param_name   = $matches[1];
                        $regex        = (!isset($matches[3]) || $matches[3] === '') ? null: $matches[3];
                        $is_optional  = isset($matches[4]) && $matches[4] === '?';

                        if($path_part !== '')
                        {
                            if($regex !== null && !preg_match('/^' . $regex . '$/', $path_part))
                            {
                                $matched = false;
                                break;
                            }
                            $params[$param_name] = $path_part;
                            $j++;
                        }
                        else
                        if(!$is_optional)
                        {
                            $matched = false;
                            break;
                        }
                        else
                        {
                            $params[$param_name] = null;
                        }
                        $i++;
                    }
                    else
                    {
                        if($part !== $path_part)
                        {
                            $matched = false;
                            break;
                        }
                        $i++;
                        $j++;
                    }
                }

                // 残りのパターンがすべてオプションなら許容
                while($i < count($pattern_parts))
                {
                    $part = $pattern_parts[$i];
                    if(preg_match('/^:([a-zA-Z0-9_]+)(\((.+)\))?\?$/', $part, $matches))
                    {
                        $param_name = $matches[1];
                        $params[$param_name] = null;
                        $i++;
                    }
                    else
                    {
                        $matched = false;
                        break;
                    }
                }

                if($matched && $j === count($path_parts))
                {
                    $p_param->setTempBuff(['__req-ins' => $p_dat]);
                    $p_param->setTempBuff(['__req-params' => $params]);

                    foreach($this->parsers as $type => $parser)
                    {
                        $content_type = $this->request->getHeaderLine('Content-Type');
                        if(stripos($content_type, $type) !== false)
                        {
                            $method_name = $parser->selector($p_param->request(), $this->request->getUri()->getPath());
                            $parsed_body = $parser->$method_name($this->request->getBody()->__toString());
                            $this->request = $this->request->withParsedBody($parsed_body);
                            $p_param->setTempBuff(['__req-ins' => $this->request]);
                            break;
                        }
                    }

                    return $route['event'];
                }
            }

            if($this->routing['mismatch'] === null)
            {
                $p_param->setTempBuff(['__req-ins' => $p_dat]);
                $body = [
                    'error' => __('error.MESSAGE_REASON_NOT_FOUND'),
                    'message' => __('error.MESSAGE_BODY_ROUTING_MISMATCH'),
                    'status' => 404
                ];
                $p_param->response()->status($body['status'], $body['error'])
                    ->json($body);
                return null;
            }
            return $this->routing['mismatch'];
        };
    }

    /**
     * 緊急停止時のコールバックの取得
     * 
     * 例外等の緊急切断時に実行される。nullを返す場合は無効化となる。
     * 
     * @return mixed "function(SocketManagerParameter $p_param)"
     */
    public function getEmergencyCallback()
    {
        return null;
    }

    /**
     * UNITパラメータインスタンスの取得
     * 
     * nullの場合はSocketManagerParameterのインスタンスが適用される
     * 
     * @return ?SocketManagerParameter SocketManagerParameterクラスのインスタンス（※1）
     * @see:RETURN （※1）当該クラス、あるいは当該クラスを継承したクラスも指定可
     */
    public function getUnitParameter(): ?ParameterForRestApi
    {
        return $this->unit_parameter;
    }
}
