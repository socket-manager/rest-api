<?php
/**
 * アップロードファイルラッパークラスのファイル
 * 
 */

namespace App\Common;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

/**
 * アップロードファイルラッパークラス
 * 
 */
class UploadedFileWrapper
{
    /**
     * @var UploadedFileInterface アップロードファイルインスタンス
     */
    private UploadedFileInterface $file;

    /**
     * コンストラクタ
     * 
     * @param UploadedFileInterface $p_file アップロードファイルインスタンス
     */
    public function __construct(UploadedFileInterface $p_file)
    {
        $this->file = $p_file;
    }

    public function name(): string
    {
        return $this->file->getClientFilename() ?? '';
    }

    public function type(): string
    {
        return $this->file->getClientMediaType() ?? '';
    }

    public function size(): int
    {
        return $this->file->getSize() ?? 0;
    }

    public function error(): int
    {
        return $this->file->getError();
    }

    public function stream(): StreamInterface
    {
        return $this->file->getStream();
    }

    public function moveTo(string $targetPath): void
    {
        $this->file->moveTo($targetPath);
    }
}
