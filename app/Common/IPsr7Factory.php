<?php
/**
 * ファクトリーインターフェースのファイル
 * 
 * PSR-7準拠ファクトリーインターフェースのファイル
 */

namespace App\Common;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;


/**
 * PSR-7準拠ファクトリーインターフェース
 * 
 */
interface IPsr7Factory
{
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
    public function createRequestFromRaw(...$p_args): ServerRequestInterface;

    /**
     * ResponseInterfaceインスタンスの生成
     * 
     * @return ResponseInterface インスタンス
     */
    public function createResponse(): ResponseInterface;

    /**
     * StreamInterfaceインスタンスの生成
     * 
     * @param string $content ボディ部のコンテンツ
     * @return StreamInterface Streamインターフェース
     */
    public function createStream(string $content = ''): StreamInterface;
}
