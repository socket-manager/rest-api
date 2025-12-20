<?php
/**
 * PSR-7準拠ファクトリークラスのファイル
 * 
 * Laminas/Diactorosライブラリ用のファイル
 */

namespace App\Common;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Uri;
use Laminas\Diactoros\UploadedFile;


/**
 * Laminas/Diactorosライブラリ用のファクトリークラス
 * 
 */
class LaminasFactory implements IPsr7Factory
{
    //--------------------------------------------------------------------------
    // プロパティ
    //--------------------------------------------------------------------------


    //--------------------------------------------------------------------------
    // メソッド
    //--------------------------------------------------------------------------

    /**
     * コンストラクタ
     * 
     */
    public function __construct()
    {
    }

    /**
     * ServerRequestInterfaceインスタンスの生成
     *
     * 可変引数の想定:
     *   [0] array  $p_meta   (__meta)
     *   [1] string $p_body_content
     *   [2] array  $p_files  (連想配列形式: name => [filename, headers, content])
     *   [3] array  $p_fields (フォームデータ)
     *   [4] array  $p_cookies
     */
    public function createRequestFromRaw(...$p_args): ServerRequestInterface
    {
        $meta         = (array)$p_args[0] ?? [];
        $body_content = $p_args[1] ?? '';
        $files_raw    = (array)$p_args[2] ?? [];
        $fields       = $p_args[3] ?? [];
        $cookies      = (array)$p_args[4] ?? [];

        // ボディストリーム
        $stream = new Stream('php://temp', 'rw');
        if($body_content !== '')
        {
            $stream->write($body_content);
            $stream->rewind();
        }

        // URI生成とクエリパラメータ抽出をファクトリ内で完結
        $uri = $this->createUri($meta['__meta']['uri'] ?? '/');
        $query_params = [];
        if($uri->getQuery() !== '')
        {
            parse_str($uri->getQuery(), $query_params);
        }

        // UploadedFileInterface 配列に変換
        $uploaded_files = [];
        foreach($files_raw as $name => $file_info)
        {
            $file_stream = new Stream('php://temp', 'rw');
            $file_stream->write($file_info['content'] ?? '');
            $file_stream->rewind();

            $uploaded_files[$name] = new UploadedFile(
                $file_stream,
                strlen($file_info['content'] ?? ''),
                UPLOAD_ERR_OK,
                $file_info['filename'] ?? null,
                $file_info['headers']['content-type'][0] ?? null
            );
        }

        return new ServerRequest(
            [],                                     // $_SERVER 相当
            $uploaded_files,                        // UploadedFileInterface[]
            $uri,                                   // URI
            $meta['__meta']['method'] ?? 'GET',     // HTTP method
            $stream,                                // ボディ
            $meta['__meta']['headers'] ?? [],       // ヘッダ配列
            $cookies,                               // cookies
            $query_params,                          // query params
            $fields,                                // parsed body
            $meta['__meta']['protocol'] ?? '1.1'    // HTTP protocol version
        );
    }

    /**
     * ResponseInterfaceインスタンスの生成
     * 
     * @return ResponseInterface インスタンス
     */
    public function createResponse(): ResponseInterface
    {
        return new Response();
    }

    /**
     * Uriインスタンスの生成
     * 
     * @return Uri クラスインスタンス
     */
    private function createUri(string $p_uri): Uri
    {
        return new Uri($p_uri);
    }

    /**
     * StreamInterfaceインスタンスの生成
     * 
     * @param string $content ボディ部のコンテンツ
     * @return StreamInterface Streamインターフェース
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = new \Laminas\Diactoros\Stream('php://temp', 'w+');
        $stream->write($content);
        $stream->rewind();

        return $stream;
    }
}
