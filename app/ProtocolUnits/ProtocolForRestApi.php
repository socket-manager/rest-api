<?php
/**
 * ステータスUNIT登録クラスのファイル
 * 
 * SocketManagerのsetProtocolUnitsメソッドへ引き渡されるクラスのファイル
 */

namespace App\ProtocolUnits;

use Psr\Http\Message\ServerRequestInterface;
use SocketManager\Library\IEntryUnits;
use SocketManager\Library\ProtocolQueueEnum;
use SocketManager\Library\SocketManagerParameter;

use App\ProtocolUnits\ProtocolForRestApiStatusEnum;
use App\UnitParameter\ParameterForRestApi;


/**
 * プロトコルUNIT登録クラス
 * 
 * IEntryUnitsインタフェースをインプリメントする
 */
class ProtocolForRestApi implements IEntryUnits
{
    /**
     * @param ?int $p_limit_request_size リクエスト（ヘッダ＋ボディ）制限サイズ
     */
    protected ?int $limit_request_size;

    /**
     * @param ?int $p_limit_body_size ボディ部制限サイズ
     */
    protected ?int $limit_body_size;

    /**
     * @var bool KeepAlive フラグ（true：ON or false：OFF）
     */
    protected bool $keep_alive;

    /**
     * @var array メソッドリスト
     */
    protected array $methods;

    /**
     * @var array CORS定義
     */
    protected array $cors;

    /**
     * @var ?string Expectヘッダ受信時のハンドラ名
     */
    protected ?string $expect_handler;

    /**
     * @var const QUEUE_LIST キュー名のリスト
     */
    protected const QUEUE_LIST = [
        ProtocolQueueEnum::RECV->value,		// 受信処理のキュー
        ProtocolQueueEnum::SEND->value,		// 送信処理のキュー
        ProtocolQueueEnum::ALIVE->value		// アライブチェック処理のキュー
    ];


    /**
     * コンストラクタ
     * 
     * @param ?int $p_limit_request_size リクエスト（ヘッダ＋ボディ）制限サイズ
     * @param ?int $p_limit_body_size ボディ部制限サイズ
     * @param bool $p_keep_alive KeepAlive フラグ（true：ON or false：OFF）
     * @param array $p_methods メソッドリスト
     * @param array $p_cors CORS定義
     * @param ?string $p_expect_handler Expectヘッダ受信時のハンドラ名
     */
    public function __construct(?int $p_limit_request_size, ?int $p_limit_body_size, bool $p_keep_alive, array $p_methods, array $p_cors, ?string $p_expect_handler)
    {
        $this->limit_request_size = $p_limit_request_size;
        $this->limit_body_size = $p_limit_body_size;
        $this->keep_alive = $p_keep_alive;
        $this->methods = $p_methods;
        $this->cors = $p_cors;
        $this->expect_handler = $p_expect_handler;
    }

    /**
     * キューリストの取得
     * 
     * @return array キュー名のリスト
     */
    public function getQueueList(): array
    {
        return (array)static::QUEUE_LIST;
    }

    /**
     * ステータスUNITリストの取得
     * 
     * @param string $p_que キュー名
     * @return array キュー名に対応するUNITリスト
     */
    public function getUnitList(string $p_que): array
    {
        $ret = [];

        if($p_que === ProtocolQueueEnum::RECV->value)
        {
            $ret[] = [
                'status' => ProtocolForRestApiStatusEnum::START->value,
                'unit' => $this->getRecvStart()
            ];
            $ret[] = [
                'status' => ProtocolForRestApiStatusEnum::HEADER->value,
                'unit' => $this->getRecvHeader()
            ];
            $ret[] = [
                'status' => ProtocolForRestApiStatusEnum::HEADER_EXPECT->value,
                'unit' => $this->getRecvHeaderExpect()
            ];
            $ret[] = [
                'status' => ProtocolForRestApiStatusEnum::BODY_LENGTH->value,
                'unit' => $this->getRecvBodyLength()
            ];
            $ret[] = [
                'status' => ProtocolForRestApiStatusEnum::BODY_MULTIPART->value,
                'unit' => $this->getRecvBodyMultipart()
            ];
            $ret[] = [
                'status' => ProtocolForRestApiStatusEnum::BODY_CHUNKED->value,
                'unit' => $this->getRecvBodyChunked()
            ];
            $ret[] = [
                'status' => ProtocolForRestApiStatusEnum::BUILD_REQUEST->value,
                'unit' => $this->getRecvBuildRequest()
            ];
        }
        else
        if($p_que === ProtocolQueueEnum::SEND->value)
        {
            $ret[] = [
                'status' => ProtocolForRestApiStatusEnum::START->value,
                'unit' => $this->getSendStart()
            ];
            $ret[] = [
                'status' => ProtocolForRestApiStatusEnum::SENDING->value,
                'unit' => $this->getSendSending()
            ];
        }
        else
        if($p_que === ProtocolQueueEnum::ALIVE->value)
        {
            $ret[] = [
                'status' => ProtocolForRestApiStatusEnum::START->value,
                'unit' => $this->getAliveStart()
            ];
        }

        return $ret;
    }


    /**
     * 共通処理
     */

    /**
     * OPTIONSメソッドのレスポンス送信
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @param ServerRequestInterface $p_request リクエストインスタンス
     * @return ?string 遷移先のステータス名
     */
    protected function sendOptionsResponse(ParameterForRestApi $p_param, ServerRequestInterface $p_request): void
    {
        $p_param->setTempBuff(['__req-ins' => $p_request]);
        if(count($this->cors))
        {
            $p_param->response()->status(204, __('error.MESSAGE_REASON_NO_CONTENT'));

            // Access-Control-Allow-Origin の決定
            if(in_array('*', $this->cors['allow_origins']))
            {
                // 一律許可
                $p_param->response()->header('Access-Control-Allow-Origin', '*');
            }
            else
            if($p_request->getHeader('Origin') === [] && in_array('null', $this->cors['allow_origins']))
            {
                // リクエストに Origin ヘッダがなく、null を許可している場合
                $p_param->response()->header('Access-Control-Allow-Origin', 'null');
            }
            else
            if(in_array($p_request->getHeader('Origin')[0], $this->cors['allow_origins']))
            {
                // 許可リストに含まれている場合、その値を返す
                $p_param->response()->header('Access-Control-Allow-Origin', $p_request->getHeader('Origin')[0]);
            }

            // 複数値はカンマ区切りでまとめて返す
            $p_param->response()->header('Access-Control-Allow-Methods', implode(', ', $this->methods) . ', OPTIONS');
            $p_param->response()->header('Access-Control-Allow-Headers', implode(', ', $this->cors['allow_headers']));

            // 送信処理
            $p_param->response()->send();
            $p_param->response(true);

            // optionsフラグをディスクリプタへ設定
            $p_param->setTempBuff(['__method-options' => true]);
        }
        else
        {
            $p_param->response()->status(405, __('error.MESSAGE_REASON_METHOD_NOT_ALLOWED'))
                ->header('Allow', implode(", ", $this->methods))
                ->send();
        }
    }


    /**
     * 以降はステータスUNITの定義（"RECV"キュー）
     */

    /**
     * ステータス名： START
     * 
     * 処理名：受信開始
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @return ?string 遷移先のステータス名
     */
    protected function getRecvStart()
    {
        return function(ParameterForRestApi $p_param): ?string
        {
            // テンポラリバッファの準備
            $p_param->setTempBuff([
                '__header' => ['buf' => '', 'rows' => [], 'length' => 0],
                '__body'   => ['buf' => '', 'length' => 0, 'chunked' => false, 'multipart' => false],
                '__meta'   => ['method' => '', 'uri' => '', 'protocol' => '', 'headers' => []]
            ]);

            return ProtocolForRestApiStatusEnum::HEADER->value;
        };
    }

    /**
     * ステータス名： HEADER
     * 
     * 処理名：ヘッダ受信
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @return ?string 遷移先のステータス名
     */
    protected function getRecvHeader()
    {
        return function(ParameterForRestApi $p_param): ?string
        {
            $rcv = '';
            $siz = $p_param->protocol()->recv($rcv);
            if($siz === 0)
            {
                $p_param->emergencyShutdown();
                return null;
            }
            else
            if($siz > 0)
            {
                $tmp = $p_param->getTempBuff(['__header']);
                $tmp['__header']['buf'] .= $rcv;

                // サイズ制限チェック
                if(strlen($tmp['__header']['buf']) >= $this->limit_request_size)
                {
                    $p_param->response()->status(413, __('error.MESSAGE_REASON_PAYLOAD_TOO_LARGE'))
                        ->send();
                    return null;
                }

                // 改行で分割
                $lines = explode("\r\n", $tmp['__header']['buf']);
                $tmp['__header']['rows'] = $lines;
                $p_param->setTempBuff($tmp);

                // ヘッダ終端判定
                if(strpos($tmp['__header']['buf'], "\r\n\r\n") !== false)
                {
                    [$raw_headers, $rest] = explode("\r\n\r\n", $tmp['__header']['buf'], 2);

                    // ヘッダ長をセット
                    $tmp['__header']['length'] = strlen($raw_headers);
                    $p_param->setTempBuff($tmp);

                    // リクエストライン解析
                    $request_line = array_shift($lines);
                    [$method, $uri, $proto] = explode(' ', $request_line);

                    $headers = [];
                    foreach($lines as $line)
                    {
                        if($line === "")
                        {
                            break;
                        }
                        if(strpos($line, ':') !== false)
                        {
                            [$name, $value] = explode(':', $line, 2);
                            $name = strtolower(trim($name));
                            $value = trim($value);

                            // 同じヘッダ名が複数ある場合 → 配列に追加
                            if (!isset($headers[$name])) {
                                $headers[$name] = [];
                            }
                            $headers[$name][] = $value;
                        }
                    }

                    $meta = $p_param->getTempBuff(['__meta']);
                    $meta['__meta']['method']   = $method;
                    $meta['__meta']['uri']      = $uri;
                    $meta['__meta']['protocol'] = $proto;
                    $meta['__meta']['headers']  = $headers;
                    $p_param->setTempBuff($meta);

                    // ボディの先頭部分を __body に保存
                    $body = $p_param->getTempBuff(['__body']);
                    $body['__body']['buf'] = $rest;
                    $p_param->setTempBuff($body);

                    $next_status = null;

                    // Content-Length判定
                    if(isset($headers['content-length']))
                    {
                        $body['__body']['length'] = (int)$headers['content-length'][0];

                        // multipart/form-data 判定
                        if(isset($headers['content-type']))
                        {
                            foreach($headers['content-type'] as $ct)
                            {
                                if(stripos($ct, 'multipart/form-data') !== false)
                                {
                                    $body['__body']['multipart'] = true;
                                    // 境界文字列を抽出
                                    if(preg_match('/boundary=(.+)$/i', $ct, $m))
                                    {
                                        $body['__body']['boundary'] = $m[1];
                                    }
                                }
                            }
                        }
                        $p_param->setTempBuff($body);
                        if($body['__body']['multipart'])
                        {
                            $next_status = ProtocolForRestApiStatusEnum::BODY_MULTIPART->value;
                        }
                        else
                        {
                            $next_status = ProtocolForRestApiStatusEnum::BODY_LENGTH->value;
                        }
                    }
                    else
                    // chunked判定
                    if(isset($headers['transfer-encoding'])
                    && in_array('chunked', array_map('strtolower', $headers['transfer-encoding'])))
                    {
                        $body['__body']['chunked'] = true;
                        $p_param->setTempBuff($body);
                        $next_status = ProtocolForRestApiStatusEnum::BODY_CHUNKED->value;
                    }
                    else
                    {
                        // GET/HEADなど → ボディなし
                        $next_status = ProtocolForRestApiStatusEnum::BUILD_REQUEST->value;
                    }

                    if(isset($headers['Expect']))
                    {
                        $p_param->setTempBuff(['__expect' => $next_status]);

                        return ProtocolForRestApiStatusEnum::HEADER_EXPECT->value;
                    }

                    return $next_status;
                }
            }

            return ProtocolForRestApiStatusEnum::HEADER->value; // 継続
        };
    }

    /**
     * ステータス名： HEADER
     * 
     * 処理名：ヘッダ（Expect）受信
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @return ?string 遷移先のステータス名
     */
    protected function getRecvHeaderExpect()
    {
        return function(ParameterForRestApi $p_param): ?string
        {
            $meta = $p_param->getTempBuff(['__meta']);
            $header = $p_param->getTempBuff(['__header']);

            // Cookie
            $cookies = [];
            if(isset($meta['__meta']['headers']['cookie']))
            {
                foreach($meta['__meta']['headers']['cookie'] as $cookie_header)
                {
                    $pairs = explode(';', $cookie_header);
                    foreach($pairs as $pair)
                    {
                        $pair = trim($pair);
                        if($pair === '')
                        {
                            continue;
                        }
                        [$name, $value] = array_map('trim', explode('=', $pair, 2));
                        $cookies[$name] = $value;
                    }
                }
            }

            // ファクトリを利用して ServerRequestInterface を生成
            $request = $p_param->factory->createRequestFromRaw(
                $meta,
                null,
                null,
                null,
                $cookies
            );

            $method = strtoupper($request->getMethod());
            if($this->expect_handler !== null && $this->expect_handler !== '')
            {
                if($method === 'OPTIONS')
                {
                    // optionsフラグをディスクリプタへ設定
                    $p_param->setTempBuff(['__method-options' => true]);
                }

                $p_param->setTempBuff(['__command_queue' => $this->expect_handler]);
                $p_param->setRecvStack($request);   // 受信バッファへ格納
            }
            else
            {
                $status = null;
                $p_param->setTempBuff(['__req-ins' => $request]);

                // HTTPメソッド検証
                if(!in_array($method, $this->methods))
                {
                    $status = 405;
                }

                // ボディサイズ検証（Content-Lengthがある場合のみ）
                $length = 0;
                $values = $request->getHeader('content-length');
                if(!empty($values))
                {
                    $length = $values[0];
                    if($length > $this->limit_body_size)
                    {
                        $status = 413;
                    }
                }

                // 全体サイズ検証
                $length += $header['__header']['length'];
                if($length > $this->limit_request_size)
                {
                    $status = 413;
                }

                $expect_values = $request->getHeader('expect');
                foreach($expect_values as $value)
                {
                    if($value !== '100-continue')
                    {
                        $status = 417;
                        break;
                    }
                }

                // エラーステータスの場合
                if($status)
                {
                    $p_param->response()->status($status)->send();
                }
                else
                if($method === 'OPTIONS')
                {
                    $p_param->setTempBuff(['__expect' => null]);
                    $this->sendOptionsResponse($p_param, $request);
                }
                else
                {
                    $p_param->response()->status(100)->send();
                }
                return null;
            }

            return null; // 状態遷移の終わり
        };
    }

    /**
     * ステータス名： BODY_LENGTH
     * 
     * 処理名：ボディ部受信（Content-Length）
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @return ?string 遷移先のステータス名
     */
    protected function getRecvBodyLength()
    {
        return function(ParameterForRestApi $p_param): ?string
        {
            $body = $p_param->getTempBuff(['__body']);

            // サイズ制限チェック
            $body_len = strlen($body['__body']['buf']);
            $header = $p_param->getTempBuff(['__header']);
            if((($header['__header']['length'] + $body_len) >= $this->limit_request_size)
            || ($body_len >= $this->limit_body_size))
            {
                $p_param->response()->status(413, __('error.MESSAGE_REASON_PAYLOAD_TOO_LARGE'))
                    ->send();
                return null;
            }

            // 既に全体が揃っているか確認
            if($body_len >= $body['__body']['length'])
            {
                return ProtocolForRestApiStatusEnum::BUILD_REQUEST->value;
            }

            $rcv = '';
            $siz = $p_param->protocol()->recv($rcv);
            if($siz === 0)
            {
                $p_param->emergencyShutdown();
                return null;
            }
            else
            if($siz > 0)
            {
                $body = $p_param->getTempBuff(['__body']);
                $body['__body']['buf'] .= $rcv;

                // サイズ制限チェック
                $body_len = strlen($body['__body']['buf']);
                if((($header['__header']['length'] + $body_len) >= $this->limit_request_size)
                || ($body_len >= $this->limit_body_size))
                {
                    $p_param->response()->status(413, __('error.MESSAGE_REASON_PAYLOAD_TOO_LARGE'))
                        ->send();
                    return null;
                }

                $p_param->setTempBuff($body);
                if($body_len >= $body['__body']['length'])
                {
                    return ProtocolForRestApiStatusEnum::BUILD_REQUEST->value;
                }
            }

            return ProtocolForRestApiStatusEnum::BODY_LENGTH->value; // 継続
        };
    }

    /**
     * ステータス名： BODY_MULTIPART
     * 
     * 処理名：ボディ部受信（multipart）
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @return ?string 遷移先のステータス名
     */
    protected function getRecvBodyMultipart()
    {
        return function(ParameterForRestApi $p_param): ?string
        {
            $body = $p_param->getTempBuff(['__body']);

            // サイズ制限チェック
            $body_len = strlen($body['__body']['buf']);
            $header = $p_param->getTempBuff(['__header']);
            if((($header['__header']['length'] + $body_len) >= $this->limit_request_size)
            || ($body_len >= $this->limit_body_size))
            {
                $p_param->response()->status(413, __('error.MESSAGE_REASON_PAYLOAD_TOO_LARGE'))
                    ->send();
                return null;
            }

            $boundary = '--' . $body['__body']['boundary'];

            // 終端境界が含まれている場合
            if(strpos($body['__body']['buf'], $boundary . "--") !== false)
            {
                $parts = explode($boundary, $body['__body']['buf']);
                $files = [];
                $fields = [];

                foreach($parts as $part)
                {
                    $part = ltrim($part, "\r\n");
                    if($part === '' || $part === "--\r\n")
                    {
                        continue;
                    }

                    if(strpos($part, "\r\n\r\n") === false)
                    {
                        continue;
                    }

                    // ヘッダとコンテンツに分割
                    [$raw_headers, $content] = explode("\r\n\r\n", $part, 2);

                    $lines = explode("\r\n", trim($raw_headers));
                    $part_headers = [];
                    foreach($lines as $line)
                    {
                        if(strpos($line, ':') !== false)
                        {
                            [$name, $value] = explode(':', $line, 2);
                            $name = strtolower(trim($name));
                            if(!isset($part_headers[$name]))
                            {
                                $part_headers[$name] = [];
                            }
                            $part_headers[$name][] = trim($value);
                        }
                    }

                    // Content-Disposition から情報を抽出
                    if(isset($part_headers['content-disposition']))
                    {
                        foreach($part_headers['content-disposition'] as $cd)
                        {
                            // ファイルアップロード
                            if(preg_match('/name="([^"]+)"; filename="([^"]+)"/', $cd, $m))
                            {
                                $files[] = [
                                    'name'     => $m[1],
                                    'filename' => $m[2],
                                    'headers'  => $part_headers,
                                    'content'  => rtrim($content, "\r\n")
                                ];
                            }
                            // 通常フォームフィールド
                            else
                            if(preg_match('/name="([^"]+)"/', $cd, $m))
                            {
                                $fields[$m[1]] = rtrim($content, "\r\n");
                            }
                        }
                    }
                }

                $p_param->setTempBuff([
                    '__files'  => $files,
                    '__fields' => $fields
                ]);

                return ProtocolForRestApiStatusEnum::BUILD_REQUEST->value;
            }

            // 不足している場合のみ受信
            $rcv = '';
            $siz = $p_param->protocol()->recv($rcv);
            if($siz === 0)
            {
                $p_param->emergencyShutdown();
                return null;
            }
            else
            if($siz > 0)
            {
                $body['__body']['buf'] .= $rcv;

                // サイズ制限チェック
                $body_len = strlen($body['__body']['buf']);
                if((($header['__header']['length'] + $body_len) >= $this->limit_request_size)
                || ($body_len >= $this->limit_body_size))
                {
                    $p_param->response()->status(413, __('error.MESSAGE_REASON_PAYLOAD_TOO_LARGE'))
                        ->send();
                    return null;
                }

                $p_param->setTempBuff($body);
            }

            return ProtocolForRestApiStatusEnum::BODY_MULTIPART->value; // 継続
        };
    }

    /**
     * ステータス名： BODY_CHUNKED
     * 
     * 処理名：ボディ部受信（Chunked）
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @return ?string 遷移先のステータス名
     */
    protected function getRecvBodyChunked()
    {
        return function(ParameterForRestApi $p_param): ?string
        {
            $body = $p_param->getTempBuff(['__body']);

            // サイズ制限チェック
            $body_len = strlen($body['__body']['buf']);
            $header = $p_param->getTempBuff(['__header']);
            if((($header['__header']['length'] + $body_len) >= $this->limit_request_size)
            || ($body_len >= $this->limit_body_size))
            {
                $p_param->response()->status(413, __('error.MESSAGE_REASON_PAYLOAD_TOO_LARGE'))
                    ->send();
                return null;
            }

            // 終端チャンクが既に含まれている場合
            if(strpos($body['__body']['buf'], "0\r\n\r\n") !== false)
            {
                // チャンクをパースしてデータ部分だけを抽出
                $accum = '';
                $buf = $body['__body']['buf'];

                while(true)
                {
                    if(strpos($buf, "\r\n") === false)
                    {
                        break;
                    }
                    [$line, $rest] = explode("\r\n", $buf, 2);
                    $size = hexdec(trim($line));

                    if($size === 0)
                    {
                        // 終端チャンク → 後続の CRLF を除外して終了
                        break;
                    }

                    if(strlen($rest) < $size + 2)
                    {
                        $p_param->response()->status(400)->send();
                        break; // データ不足。
                    }

                    $data = substr($rest, 0, $size);
                    $accum .= $data;

                    // 次のチャンクへ
                    $buf = substr($rest, $size + 2);
                }

                $p_param->setTempBuff(['__accum' => ['body' => $accum]]);
                return ProtocolForRestApiStatusEnum::BUILD_REQUEST->value;
            }

            // 不足している場合のみ受信
            $rcv = '';
            $siz = $p_param->protocol()->recv($rcv);
            if($siz > 0)
            {
                $body['__body']['buf'] .= $rcv;

                // サイズ制限チェック
                $body_len = strlen($body['__body']['buf']);
                if((($header['__header']['length'] + $body_len) >= $this->limit_request_size)
                || ($body_len >= $this->limit_body_size))
                {
                    $p_param->response()->status(413, __('error.MESSAGE_REASON_PAYLOAD_TOO_LARGE'))
                        ->send();
                    return null;
                }

                $p_param->setTempBuff($body);
            }
            return ProtocolForRestApiStatusEnum::BODY_CHUNKED->value; // 継続
        };
    }

    /**
     * ステータス名： BUILD_REQUEST
     * 
     * 処理名：リクエストインスタンス生成
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @return ?string 遷移先のステータス名
     */
    protected function getRecvBuildRequest()
    {
        return function(ParameterForRestApi $p_param): ?string
        {
            $meta   = $p_param->getTempBuff(['__meta']);
            $accum  = $p_param->getTempBuff(['__accum']);
            $body   = $p_param->getTempBuff(['__body']);
            $files  = $p_param->getTempBuff(['__files']);
            $fields = $p_param->getTempBuff(['__fields']);

            // ボディ判定
            // Content-Length の場合は __body['buf']、
            // multipart の場合は 空にする
            // chunked の場合は __accum['body'] を利用
            $body_content = '';
            if($body && $body['__body']['multipart'])
            {
                $body_content = '';
            }
            else
            if($body && !$body['__body']['chunked'])
            {
                $body_content = $body['__body']['buf'];
            }
            else
            if($accum && isset($accum['__accum']['body']))
            {
                $body_content = $accum['__accum']['body'];
            }

            // Cookie
            $cookies = [];
            if(isset($meta['__meta']['headers']['cookie']))
            {
                foreach($meta['__meta']['headers']['cookie'] as $cookie_header)
                {
                    $pairs = explode(';', $cookie_header);
                    foreach($pairs as $pair)
                    {
                        $pair = trim($pair);
                        if($pair === '')
                        {
                            continue;
                        }
                        [$name, $value] = array_map('trim', explode('=', $pair, 2));
                        $cookies[$name] = $value;
                    }
                }
            }

            // ファクトリを利用して ServerRequestInterface を生成
            $request = $p_param->factory->createRequestFromRaw(
                $meta,
                $body_content,
                $files['__files'] ?? [],
                $fields['__fields'] ?? [],
                $cookies
            );

            // CORSレスポンス生成
            if($request->getMethod() === 'OPTIONS')
            {
                $this->sendOptionsResponse($p_param, $request);
                return null;
            }

            // 非対応のHTTPメソッド
            if(!in_array($request->getMethod(), $this->methods))
            {
                $p_param->response()->status(405, __('error.MESSAGE_REASON_METHOD_NOT_ALLOWED'))
                    ->header('Allow', implode(", ", $this->methods))
                    ->send();
                return null;
            }

            $p_param->setRecvStack($request);   // 受信バッファへ格納

            return null; // 状態遷移の終わり
        };
    }


    /**
     * 以降はステータスUNITの定義（"SEND"キュー）
     */

    /**
     * 共通ロジック
     * 
     * 処理名：継続送信
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @param ?string $p_status 継続中の遷移先
     * @return ?string 遷移先のステータス名
     */
    private function sending(ParameterForRestApi $p_param, ?string $p_status): ?string
    {
        // データ送信
        $w_ret = $p_param->protocol()->sending();

        // 送信中の場合は再実行
        if($w_ret === null)
        {
            return $p_status;
        }

        $err = $p_param->getTempBuff(['__error-send']);
        $opt = $p_param->getTempBuff(['__method-options']);

        // Expectヘッダがある時
        $exp = $p_param->getTempBuff(['__expect']);
        if(
            ($exp !== null && $exp['__expect'] !== null)
        &&  ($err !== null && $err['__error-send'] !== null)
        )
        {
            if($opt !== null && $opt['__method-options'] === true)
            {
                $this->sendOptionsResponse($p_param, $p_param->request()->getRequestInterface());
            }
            return null;
        }

        $str = $p_param->getTempBuff(['__stream-send']);
        if($str !== null && $str['__stream-send'] !== null)
        {
            $stream_send = $str['__stream-send'];
            $now_flg = array_shift($stream_send);
            if($now_flg === true)
            {
                $p_param->setTempBuff(['__stream-send' => $stream_send]);
                return null;
            }
            $p_param->setTempBuff(['__stream-send' => null]);
            $p_param->emergencyShutdown();
        }

        if($err !== null && $err['__error-send'] === true)
        {
            $p_param->emergencyShutdown();
            return null;
        }

        if($opt !== null && $opt['__method-options'] === true)
        {
            $p_param->setTempBuff(['__method-options' => false]);
            return null;
        }
        
        if($this->keep_alive === false)
        {
            $p_param->emergencyShutdown();
        }

        return null;
    }

    /**
     * ステータス名： START
     * 
     * 処理名：送信開始
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @return ?string 遷移先のステータス名
     */
    protected function getSendStart()
    {
        return function(ParameterForRestApi $p_param): ?string
        {
            // 送信データスタックから取得
            $dat = $p_param->protocol()->getSendData();
            if($dat === null)
            {
                return null;
            }
            $p_param->protocol()->setSendingData($dat);

            // 送信実行
            return $this->sending($p_param, ProtocolForRestApiStatusEnum::SENDING->value);
        };
    }

    /**
     * ステータス名： SENDING
     * 
     * 処理名：送信実行
     * 
     * @param ParameterForWebsocket $p_param UNITパラメータ
     * @return ?string 遷移先のステータス名
     */
    protected function getSendSending()
    {
        return function(ParameterForRestApi $p_param): ?string
        {
            // 送信実行
            return $this->sending($p_param, $p_param->getStatusName());
        };
    }


    /**
     * 以降はステータスUNITの定義（"ALIVE"キュー）
     */

    /**
     * ステータス名： START
     * 
     * 処理名：アライブチェック開始
     * 
     * @param SocketManagerParameter $p_param UNITパラメータ
     * @return ?string 遷移先のステータス名
     */
    protected function getAliveStart()
    {
        return function(ParameterForRestApi $p_param): ?string
        {
            $str = $p_param->getTempBuff(['__stream-send']);
            if($str !== null && $str['__stream-send'] !== null)
            {
                return null;
            }

            if(!$p_param->request()->existRequest())
            {
                $p_param->logWriter('error', [__('error.LABEL_ILLEGAL_ACCESS') => __('error.MESSAGE_NO_REQUEST', ['cid' => $p_param->getConnectionId()])]);
            }

            $p_param->emergencyShutdown();

            return null;
        };
    }
}
