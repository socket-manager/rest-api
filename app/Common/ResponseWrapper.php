<?php
/**
 * レスポンスラッパークラスのファイル
 * 
 */

namespace App\Common;

use Psr\Http\Message\ResponseInterface;

use SocketManager\Library\UnitException;
use SocketManager\Library\UnitExceptionEnum;

use App\UnitParameter\ParameterForRestApi;


/**
 * 送信済みタイプEnum
 * 
 */
enum SentType
{
    case TEXT;                  // text/plain
    case JSON;                  // application/json
    case FILE;                  // ファイル形式のContetType
    case DOWNLOAD;              // Content-Disposition: attachment
    case RESPONSE_INTERFACE;    // ResponseInterface形式
    case CHUNKED;               // Transfer-Encoding: chunked
    case EVENT;                 // Content-Type: text/event-stream
    case END;                   // 終端コード（CHUNKED or EVENT）
}

/**
 * レスポンスラッパークラス
 * 
 */
class ResponseWrapper
{
    /**
     * @var IPsr7Factory PSR-7準拠のファクトリ
     */
    private IPsr7Factory $factory;

    /**
     * @var ResponseInterface レスポンスインスタンス
     */
    private ResponseInterface $response;

    /**
     * @var ParameterForRestApi UNITパラメータインスタンス
     */
    private ParameterForRestApi $param;

    /**
     * @var bool 送信済みフラグ（true：送信済み、false：未送信）
     */
    private bool $sent_flg = false;

    /**
     * @var ?SentType 送信済みタイプ
     */
    private ?SentType $sent_type = null;

    /**
     * @var string ETag生成モード（'strong' or 'weak'）
     */
    private string $etag;


    /**
     * コンストラクタ
     * 
     * @param ResponseInterface $p_response レスポンスインスタンス
     * @param ParameterForRestApi $p_param UNITパラメータインスタンス
     * @param string $p_etag ETag生成モード（'strong' or 'weak'）
     */
    public function __construct(IPsr7Factory $p_factory, ParameterForRestApi $p_param, string $p_etag)
    {
        $this->factory = $p_factory;
        $this->response = $this->factory->createResponse();
        $this->param = $p_param;
        $this->etag = $p_etag;
    }

    /**
     * ステータスコードの設定
     * 
     * @param int $p_code ステータスコード
     * @param string $p_reason 理由
     * @return ResponseWrapper インスタンス
     */
    public function status(int $p_code, string $p_reason = ''): self
    {
        $this->response = $this->response->withStatus($p_code, $p_reason);
        return $this;
    }

    /**
     * ステータスの取得
     * 
     * @return int ステータス
     */
    public function getStatus(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * 理由句の取得
     * 
     * @return string 理由句
     */
    public function getReason(): string
    {
        return $this->response->getReasonPhrase();
    }

    /**
     * ヘッダ行の設定
     * 
     * @param string $p_name フィールド名
     * @param string $p_value フィールド値
     * @return ResponseWrapper インスタンス
     */
    public function header(string $p_name, string $p_value): self
    {
        $this->response = $this->response->withHeader($p_name, $p_value);
        return $this;
    }

    /**
     * テキストデータを送信
     * 
     * @param string $p_data テキストデータ
     */
    public function text(string $p_data): void
    {
        if($this->sent_type !== null)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $stream = $this->factory->createStream($p_data);

        $this->response = $this->response
            ->withHeader('Content-Type', 'text/plain')
            ->withBody($stream);

        $this->send();

        $this->sent_type = SentType::TEXT;
    }

    /**
     * jsonデータを送信
     * 
     * @param array $p_data jsonデータの基になる配列値
     */
    public function json(array $p_data): void
    {
        if($this->sent_type !== null)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $body = json_encode($p_data, JSON_UNESCAPED_UNICODE);
        $stream = $this->factory->createStream($body);

        $this->response = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);

        $this->send();

        $this->sent_type = SentType::JSON;
    }

    /**
     * ファイルデータを送信
     * 
     * @param string $p_path ファイルパス
     * @return bool true（成功） or false（失敗：ファイル取得に失敗）
     */
    public function file(string $p_path): bool
    {
        if($this->sent_type !== null)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        // MIMEタイプの取得
        $mime_type = get_mime_type($p_path);

        // ファイルの取得
        $data = @file_get_contents($p_path);
        if($data === false)
        {
            return false;
        }
        $stream = $this->factory->createStream($data);

        $this->response = $this->response
            ->withHeader('Content-Type', $mime_type)
            ->withBody($stream);

        $this->send();

        $this->sent_type = SentType::FILE;

        return true;
    }

    /**
     * ファイルダウンロード
     * 
     * @param string $p_path ファイルパス
     * @param string $p_filename デフォルト指定のファイル名
     * @return bool true（成功） or false（失敗：ファイル取得に失敗）
     */
    public function download(string $p_path, string $p_filename = ''): bool
    {
        if($this->sent_type !== null)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        // MIMEタイプの取得
        $mime_type = get_mime_type($p_path);

        // フィールド値の生成
        $field_value = 'attachment';
        if($p_filename !== '')
        {
            // URLエンコード
            $encoded_filename = rawurlencode($p_filename);

            // フォールバックファイル名
            $fallback = 'download';

            $field_value .= "; filename=\"{$fallback}\"; filename*=UTF-8''{$encoded_filename}";
        }

        // ファイルの取得
        $data = @file_get_contents($p_path);
        if($data === false)
        {
            return false;
        }
        $stream = $this->factory->createStream($data);

        $this->response = $this->response
            ->withHeader('Content-Type', $mime_type)
            ->withHeader('Content-Disposition', $field_value)
            ->withBody($stream);

        $this->send();

        $this->sent_type = SentType::DOWNLOAD;

        return true;
    }

    /**
     * HTMLファイルを送信
     * 
     * @param string $p_path ファイルパス
     * @return bool true（成功） or false（失敗：ファイル取得に失敗）
     */
    public function html(string $p_path): bool
    {
        if($this->sent_type !== null)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $body = @file_get_contents($p_path);
        if($body === false)
        {
            return false;
        }

        $this->response->getBody()->write($body);
        $this->response = $this->response->withHeader('Content-Type', 'text/html; charset=utf-8');

        $this->send();

        return true;
    }

    /**
     * Javascriptファイルを送信
     * 
     * @param string $p_path ファイルパス
     * @return bool true（成功） or false（失敗：ファイル取得に失敗）
     */
    public function javascript(string $p_path): bool
    {
        if($this->sent_type !== null)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $body = @file_get_contents($p_path);
        if($body === false)
        {
            return false;
        }

        $this->response->getBody()->write($body);
        $this->response = $this->response->withHeader('Content-Type', 'application/javascript; charset=utf-8');

        $this->send();

        return true;
    }

    /**
     * CSSファイルを送信
     * 
     * @param string $p_path ファイルパス
     * @return bool true（成功） or false（失敗：ファイル取得に失敗）
     */
    public function css(string $p_path): bool
    {
        if($this->sent_type !== null)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $body = @file_get_contents($p_path);
        if($body === false)
        {
            return false;
        }

        $this->response->getBody()->write($body);
        $this->response = $this->response->withHeader('Content-Type', 'text/css; charset=utf-8');

        $this->send();

        return true;
    }

    /**
     * チャンク転送データを送信
     * 
     * @param mixed $p_data チャンクデータ（文字列 or 配列/オブジェクト）
     */
    public function chunked($p_data): void
    {
        if($this->sent_type !== null && $this->sent_type !== SentType::CHUNKED)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $stream_send = [];
        $tmp = $this->param->getTempBuff(['__stream-send']);
        if($tmp === null)
        {
            $stream_send = [true];
        }
        else
        {
            $stream_send = $tmp['__stream-send'];
            $stream_send[] = true;
        }
        $this->param->setTempBuff(['__stream-send' => $stream_send]);

        if(is_array($p_data) || is_object($p_data))
        {
            $p_data = json_encode($p_data, JSON_UNESCAPED_UNICODE);
        }
        $body = sprintf("%x\r\n%s\r\n", strlen($p_data), $p_data);
        if($this->sent_type === null)
        {
            $this->sent_type = SentType::CHUNKED;

            $stream = $this->factory->createStream($body);
            $this->response = $this->response
                ->withHeader('Transfer-Encoding', 'chunked')
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Cache-Control', 'no-cache')
                ->withHeader('Connection', 'keep-alive')
                ->withHeader('X-Accel-Buffering', 'no')
                ->withBody($stream);

            $this->send();
            return;
        }

        $this->param->setSendStack($body);
    }

    /**
     * Server-Sent Events (SSE)を送信
     *
     * @param mixed $p_data data: に入れる内容（文字列 or 配列/オブジェクト）
     * @param ?string $p_event event名（任意）
     * @param ?string $p_id イベントID（任意）
     * @param ?int $p_retry 再接続までのミリ秒（任意）
     */
    public function event($p_data, ?string $p_event = null, ?string $p_id = null, ?int $p_retry = null): void
    {
        if($this->sent_type !== null && $this->sent_type !== SentType::EVENT)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $lines = [];

        if($p_event !== null)
        {
            $lines[] = "event: {$p_event}";
        }

        if($p_id !== null)
        {
            $lines[] = "id: {$p_id}";
        }

        if($p_retry !== null)
        {
            $lines[] = "retry: {$p_retry}";
        }

        if(is_array($p_data) || is_object($p_data))
        {
            $p_data = json_encode($p_data, JSON_UNESCAPED_UNICODE);
        }

        // data は複数行も許容される
        foreach(explode("\n", (string)$p_data) as $line)
        {
            $lines[] = "data: {$line}";
        }

        $stream_send = [];
        $tmp = $this->param->getTempBuff(['__stream-send']);
        if($tmp === null)
        {
            $stream_send = [true];
        }
        else
        {
            $stream_send = $tmp['__stream-send'];
            $stream_send[] = true;
        }
        $this->param->setTempBuff(['__stream-send' => $stream_send]);

        // SSE の終端は必ず空行
        $body = implode("\n", $lines) . "\n\n";

        if($this->sent_type === null)
        {
            $this->sent_type = SentType::EVENT;

            $stream = $this->factory->createStream($body);

            $this->response = $this->response
                ->withHeader('Content-Type', 'text/event-stream')
                ->withHeader('Cache-Control', 'no-cache')
                ->withHeader('Connection', 'keep-alive')
                ->withHeader('X-Accel-Buffering', 'no')
                ->withBody($stream);

            $this->send();
            return;
        }

        $this->param->setSendStack($body);
    }

    /**
     * Rangeヘッダ共通処理
     *
     * @param string $p_content_type Content-Typeヘッダ
     * @param int $p_size データサイズ
     * @param callable $p_header Range指定のボディを返す
     * @return ResponseInterface レスポンスインスタンス
     */
    private function handleRangeResponse(string $p_content_type, int $p_size, callable $p_header): ResponseInterface
    {
        $range_header = $this->param->request()->header('Range');

        // Range ヘッダがない → 全体返却
        if(!$range_header)
        {
            $body = $p_header(0, $p_size);
            $this->response = $this->response
                ->withStatus(200)
                ->withHeader('Content-Type', $p_content_type)
                ->withHeader('Content-Length', (string)$p_size);

            $this->response->getBody()->write($body);
            return $this->response;
        }

        // Range: bytes=start-end
        if(!preg_match('/bytes=(\d*)-(\d*)/', $range_header, $matches))
        {
            $this->response = $this->response
                ->withStatus(416)
                ->withHeader('Content-Range', "bytes */{$p_size}");
            return $this->response;
        }

        $start = ($matches[1] !== '') ? intval($matches[1]) : 0;
        $end   = ($matches[2] !== '') ? intval($matches[2]) : ($p_size - 1);

        // 範囲チェック
        if($start >= $p_size || $start > $end)
        {
            $this->response = $this->response
                ->withStatus(416)
                ->withHeader('Content-Range', "bytes */{$p_size}");
            return $this->response;
        }

        $end = min($end, $p_size - 1);
        $length = $end - $start + 1;

        // 部分読み出し
        $chunk = $p_header($start, $length);

        $this->response = $this->response
            ->withStatus(206)
            ->withHeader('Content-Type', $p_content_type)
            ->withHeader('Content-Length', (string)$length)
            ->withHeader('Content-Range', "bytes {$start}-{$end}/{$p_size}");

        $this->response->getBody()->write($chunk);
        return $this->response;
    }

    /**
     * Range範囲のバイナリ指定
     * 
     * @param string $p_binary バイナリデータ
     * @param string $p_content_type Content-Typeヘッダ
     */
    public function rangeBinary(string $p_binary, string $p_content_type = 'application/octet-stream'): void
    {
        if($this->sent_type !== null)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $size = strlen($p_binary);

        $this->handleRangeResponse(
            $p_content_type,
            $size,
            function($start, $length) use ($p_binary)
            {
                return substr($p_binary, $start, $length);
            }
        );

        $this->send();
    }

    /**
     * Range範囲のファイル指定
     * 
     * @param string $p_file_path ファイルパス
     * @param ?string $p_content_type Content-Typeヘッダ
     * @return bool true（成功） or false（失敗：ファイル取得に失敗）
     */
    public function rangeFile(string $p_file_path, ?string $p_content_type = null): bool
    {
        if($this->sent_type !== null)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        if(!is_file($p_file_path))
        {
            return false;
        }

        $size = filesize($p_file_path);
        $fp = fopen($p_file_path, 'rb');

        if(!$p_content_type)
        {
            $p_content_type = get_mime_type($p_file_path);
        }

        $this->handleRangeResponse(
            $p_content_type,
            $size,
            function($start, $length) use ($fp)
            {
                fseek($fp, $start);
                return fread($fp, $length);
            }
        );

        $this->send();

        return true;
    }

    /**
     * PSR-7準拠のレスポンス送信
     * 
     * @param ResponseInterface $p_response レスポンスインスタンス
     */
    public function sendResponseInterface(ResponseInterface $p_response): void
    {
        if($this->sent_type !== null)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $this->response = $p_response;
        
        $this->send();

        $this->sent_type = SentType::RESPONSE_INTERFACE;
    }

    /**
     * レスポンスデータの送信スタックへの設定
     * 
     */
    public function send(): void
    {
        if($this->sent_flg === true)
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $body = (string)$this->response->getBody();

        $request = $this->param->request();
        $exit_request = $request->existRequest();
        if($exit_request)
        {
            $method  = strtoupper($request->method());
            // ----------------------------------------------------
            // ヘルパー関数（複数 ETag パース）
            // ----------------------------------------------------
            $parse_etags = function(string $header): array {
                return array_filter(array_map('trim', explode(',', $header)), fn($v) => $v !== '');
            };

            // ----------------------------------------------------
            // If-Match / If-Unmodified-Since（更新系の楽観ロック）
            // ----------------------------------------------------
            if(in_array($method, ['PUT', 'PATCH', 'DELETE'], true))
            {
                // ---------------- If-Match ----------------
                $if_match = $request->header('If-Match');
                if($if_match !== null)
                {
                    // ETag 自動生成
                    if(!$this->response->hasHeader('ETag'))
                    {
                        $etag = '"';
                        if($this->etag === 'weak')
                        {
                            $etag .= 'W/';
                        }
                        $etag .= md5($body) . '"';
                        $this->response = $this->response->withHeader('ETag', $etag);
                    }
                    else
                    {
                        $etag = $this->response->getHeaderLine('ETag');
                    }

                    $client_etags = $parse_etags($if_match);

                    $match = false;
                    foreach($client_etags as $client)
                    {
                        if($client === '*' || $client === $etag)
                        {
                            $match = true;
                            break;
                        }
                    }

                    if(!$match)
                    {
                        $this->response = $this->response->withStatus(412);
                    }
                }

                // ---------------- If-Unmodified-Since ----------------
                $if_unmod = $request->header('If-Unmodified-Since');
                if($if_unmod !== null)
                {
                    if(!$this->response->hasHeader('Last-Modified'))
                    {
                        $this->response = $this->response->withHeader(
                            'Last-Modified',
                            gmdate('D, d M Y H:i:s') . ' GMT'
                        );
                    }

                    $last_mod = strtotime($this->response->getHeaderLine('Last-Modified'));
                    $client_t = strtotime($if_unmod);

                    if($last_mod > $client_t)
                    {
                        $this->response = $this->response->withStatus(412);
                    }
                }
            }

            // ----------------------------------------------------
            // If-None-Match（ETag ベースの 304）
            // ----------------------------------------------------
            $if_none_match = $request->header('If-None-Match');
            $etag = null;

            if($if_none_match !== null)
            {
                // ETag 自動生成
                if(!$this->response->hasHeader('ETag'))
                {
                    $etag = '"';
                    if($this->etag === 'weak')
                    {
                        $etag .= 'W/';
                    }
                    $etag .= md5($body) . '"';
                    $this->response = $this->response->withHeader('ETag', $etag);
                }
                else
                {
                    $etag = $this->response->getHeaderLine('ETag');
                }

                // Last-Modified 自動生成
                if(!$this->response->hasHeader('Last-Modified'))
                {
                    $this->response = $this->response->withHeader(
                        'Last-Modified',
                        gmdate('D, d M Y H:i:s') . ' GMT'
                    );
                }

                // 複数 ETag をパース
                $client_etags = $parse_etags($if_none_match);

                // Weak/Strong 無視して比較
                foreach($client_etags as $client)
                {
                    if($client === $etag || $client === 'W/' . $etag)
                    {
                        $this->response = $this->response->withStatus(304);
                        break;
                    }
                }
            }

            // ----------------------------------------------------
            // If-Modified-Since（Last-Modified ベースの 304）
            //     ※ If-None-Match がある場合は無視（HTTP仕様）
            // ----------------------------------------------------
            if($if_none_match === null)
            {
                $if_mod_since = $request->header('If-Modified-Since');

                if($if_mod_since !== null)
                {
                    if(!$this->response->hasHeader('Last-Modified'))
                    {
                        $this->response = $this->response->withHeader(
                            'Last-Modified',
                            gmdate('D, d M Y H:i:s') . ' GMT'
                        );
                    }

                    $last_mod = strtotime($this->response->getHeaderLine('Last-Modified'));
                    $client_t = strtotime($if_mod_since);

                    if($last_mod <= $client_t)
                    {
                        $this->response = $this->response->withStatus(304);
                    }
                }
            }
        }

        $send_data = '';

        $status = $this->response->getStatusCode();
        $send_data .= sprintf(
            "HTTP/1.1 %d %s\r\n",
            $status,
            $this->response->getReasonPhrase()
        );

        if($status >= 400)
        {
            $this->param->setTempBuff(['__error-send' => true]);
            $this->response = $this->response->withHeader('Connection', 'Close');
        }

        foreach($this->response->getHeaders() as $name => $values)
        {
            foreach($values as $value)
            {
                $send_data .= "{$name}: {$value}\r\n";
            }
        }

        if($status !== 204 && $status !== 304 && $this->sent_type !== SentType::CHUNKED && $this->sent_type !== SentType::EVENT)
        {
            $body_len = strlen($body);
            $send_data .= "Content-Length: {$body_len}\r\n";
        }
        if($status === 204 || $status === 304)
        {
            $body = '';
        }
        $send_data .= "Date: ". gmdate('D, d M Y H:i:s') . " GMT\r\n";

        $send_data .= "\r\n";
        $send_data .= $body;

        $this->param->setSendStack($send_data);

        $this->sent_flg = true;
    }

    /**
     * ストリームタイプの送信終了
     * 
     */
    public function end(): void
    {
        if($this->sent_type === SentType::EVENT)
        {
        }
        else
        if($this->sent_type === SentType::CHUNKED)
        {
            $this->param->setSendStack("0\r\n\r\n");
        }
        else
        {
            // 送信済みレスポンスのため終了
            throw new UnitException(__('system_error.MESSAGE_MULTIPLEX_TRANSMISSION'), UnitExceptionEnum::ECODE_FINISH_SHUTDOWN->value, $this->param);
        }

        $tmp = $this->param->getTempBuff(['__stream-send']);
        if($tmp === null)
        {
            $stream_send = [false];
        }
        else
        {
            $stream_send = $tmp['__stream-send'];
            $stream_send[] = false;
        }
        $this->param->setTempBuff(['__stream-send' => $stream_send]);

        $this->sent_type = SentType::END;

    }
}
