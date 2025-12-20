<?php
/**
 * レスポンスラッパークラスのファイル
 * 
 */

namespace App\Common;

use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * リクエストラッパークラス
 * 
 */
class RequestWrapper
{
    /**
     * @var ServerRequestInterface リクエストインスタンス
     */
    private ?ServerRequestInterface $request = null;

    /**
     * @var array リクエストパラメータ
     */
    private array $params = [];

    /**
     * コンストラクタ
     * 
     * @param ?ServerRequestInterface $p_request リクエストインスタンス
     */
    public function __construct(?ServerRequestInterface $p_request = null)
    {
        if($p_request !== null)
        {
            $this->request = $p_request;
            $this->params = ['route_params' => []];
        }
    }

    /**
     * リクエストインスタンスの設定
     * 
     * @param ServerRequestInterface $p_request リクエストインスタンス
     */
    public function setInstance($p_request): void
    {
        if($p_request === null)
        {
            $this->params = ['route_params' => []];
        }
        $this->request = $p_request;
    }

    /**
     * リクエストの存在フラグ
     * 
     * @param bool リクエストの存在フラグ（true:存在する or false:存在しない）
     */
    public function existRequest(): bool
    {
        if($this->request)
        {
            return true;
        }
        return false;
    }

    /**
     * リクエストパラメータの設定
     * 
     * @param string $p_name パラメータ名
     * @param mixed $p_param リクエストパラメータ
     */
    public function setParam(string $p_name, $p_param): void
    {
        $this->params[$p_name] = $p_param;
    }

    /**
     * クエリパラメータの取得
     * 
     * @return array クエリパラメータ
     */
    public function query(): array
    {
        return $this->request->getQueryParams();
    }

    /**
     * ボディ部の取得
     * 
     * @return mixed ボディ部データ
     */
    public function body(): mixed
    {
        return $this->request->getParsedBody();
    }

    /**
     * Cookieの取得
     * 
     * @return array Cookieデータ
     */
    public function cookies(): array
    {
        return $this->request->getCookieParams();
    }

    /**
     * アップロードファイルの取得
     * 
     * @return array アップロードファイル
     */
    public function files(): array
    {
        return $this->request->getUploadedFiles();
    }

    /**
     * ストリームの取得（チャンク転送用）
     * 
     * @return StreamInterface ストリームインターフェース
     */
    public function stream(): StreamInterface
    {
        return $this->request->getBody();
    }

    /**
     * バイナリデータの取得（チャンク転送用）
     * 
     * @return string|mixed バイナリデータ
     */
    public function raw(): string
    {
        return $this->request->getBody()->getContents();
    }

    /**
     * アップロードファイルインターフェースの取得（チャンク転送用）
     * 
     * @return UploadedFileInterface アップロードファイルインターフェース
     */
    public function asFile(): UploadedFileInterface
    {
        // チャンク転送で受け取ったストリーム
        $stream = $this->request->getBody();

        // サイズの取得
        $size = $stream->getSize();

        // ファイル名の取得
        $filename = null;
        $header_values = $this->request->getHeader('Content-Disposition');
        if($header_values)
        {
            // ヘッダは配列で返る可能性があるので文字列化
            $header_line = is_array($header_values) ? implode('; ', $header_values) : $header_values;

            // filename= の部分を正規表現で抽出
            if(preg_match('/filename\*?=(?:UTF-8\'\'|")?([^";]+)/i', $header_line, $matches))
            {
                // URLエンコードされている場合もあるのでデコード
                $filename = urldecode($matches[1]);
            }
        }
        if($filename === null)
        {
            $filename = 'chunked.bin';
        }

        // MIMEタイプの取得
        $values = $this->request->getHeaderLine('Content-type');
        $type = $values !== '' ? $values : null;

        // 任意のファイル名を付与
        $uploaded_file = new UploadedFile(
            $stream,
            $size,
            UPLOAD_ERR_OK,
            $filename,
            $type
        );

        return $uploaded_file;
    }

    /**
     * ヘッダ部の取得
     * 
     * @param string $p_name フィールド名
     * @return ?string フィールド値
     */
    public function header(string $p_name): ?string
    {
        $values = $this->request->getHeader($p_name);
        return $values ? implode(', ', $values) : null;
    }

    /**
     * HTTPメソッドの取得
     * 
     * @return string HTTPメソッド
     */
    public function method(): string
    {
        return $this->request->getMethod();
    }

    /**
     * URIパスの取得
     * 
     * @return string URIパス
     */
    public function path(): string
    {
        return $this->request->getUri()->getPath();
    }

    /**
     * ルートパラメータの取得
     *
     * @param ?string $p_name パラメータ名（省略時は全件）
     * @return mixed パラメータ値または配列
     */
    public function params(?string $p_name = null): mixed
    {
        if($p_name === null)
        {
            return $this->params['route_params'];
        }

        return $this->params['route_params'][$p_name] ?? null;
    }

    /**
     * PSR-7準拠のリクエスト取得
     *
     * @return ?ServerRequestInterface リクエストインスタンス
     */
    public function getRequestInterface(): ?ServerRequestInterface
    {
        return $this->request ?? null;
    }
}
