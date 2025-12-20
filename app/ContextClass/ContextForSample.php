<?php
/**
 * コンテキストクラスのファイル
 * 
 * UNITパラメータとしての利用と共にグローバル領域としても活用
 */

namespace App\ContextClass;

use Psr\Http\Message\UploadedFileInterface;

use App\UnitParameter\ParameterForRestApi;


/**
 * コンテキストクラス
 * 
 * ParameterForRestApiクラスをオーバーライドして利用する
 */
class ContextForSample extends ParameterForRestApi
{
    /**
     * @var array ユーザー情報
     */
    protected array $users = [
        '0' => ['id' => 0, 'name' => 'Taro', 'email' => 'taro@test.co.jp'],
        '1' => ['id' => 1, 'name' => 'Hanako', 'email' => 'hanako@test.co.jp']
    ];

    /**
     * @var array チャンク転送ストリーム用データ
     */
    protected array $chunked_stream = [
        "🚀 チャンク転送デモ開始！",
        "⏳ データを少しずつ送信しています…",
        "📡 これはHTTP/1.1のchunked transferです。",
        "✅ 最後のメッセージです。"
    ];

    /**
     * @var array SSEストリーム用データ
     */
    protected array $sse_stream = [
        '0' => "🚀 SSE転送デモ開始！",
        '1' => "⏳ データを少しずつ送信しています…",
        '2' => "📡 これはHTTP/1.1のtext/event-streamです。",
        '3' => "✅ 最後のメッセージです。"
    ];

    /**
     * @var bool タイムアウトフラグ
     */
    public $chunked_timeout = false;

    /**
     * 新ID取得
     * 
     * @return int 新ID
     */
    protected function getNextId(): int
    {
        $ids = array_column($this->users, 'id');
        sort($ids);
        $next_id = 0;
        foreach($ids as $id)
        {
            if($id === $next_id)
            {
                $next_id++;
            }
            else
            if($id > $next_id)
            {
                break;
            }
        }
        return $next_id;
    }

    /**
     * ユーザー一覧取得
     * 
     * @param ?int $p_id ユーザーID（nullの場合は全ユーザー指定）
     * @return array ユーザー一覧
     */
    public function getUserList(?int $p_id = null): array
    {
        $ret = [];

        if($p_id === null)
        {
            $ret = array_values($this->users);
        }
        else
        {
            if(isset($this->users[$p_id]))
            {
                $ret[] = $this->users[$p_id];
            }
        }

        return $ret;
    }

    /**
     * 新規ユーザー作成
     * 
     * @param string $p_name ユーザー名
     * @param string $p_email Eメール
     * @return array 新規ユーザー情報
     */
    public function addUser(string $p_name, string $p_email): array
    {
        $id = $this->getNextId();
        $ret = $this->users[$id] = [
            'id' => $id,
            'name' => $p_name,
            'email' => $p_email
        ];

        return $ret;
    }

    /**
     * ユーザー情報更新
     * 
     * @param int $p_id ユーザーID
     * @param ?string $p_name ユーザー名
     * @param ?string $p_email Eメール
     * @return ?array 更新後ユーザー情報 or null（更新失敗：ユーザーIDが存在しない）
     */
    public function updateUser(int $p_id, ?string $p_name, ?string $p_email): ?array
    {
        // ユーザーIDが存在しない
        if(!isset($this->users[$p_id]))
        {
            return null;
        }

        // ユーザー情報を退避
        $user = $this->users[$p_id];

        // nameの設定
        if($p_name !== null)
        {
            $user['name'] = $p_name;
        }

        // emailの設定
        if($p_email !== null)
        {
            $user['email'] = $p_email;
        }

        // ユーザー情報の反映
        $ret = $this->users[$p_id] = [
            'id' => $p_id,
            'name' => $user['name'],
            'email' => $user['email']
        ];

        return $ret;
    }

    /**
     * ユーザー削除
     * 
     * @param int $p_id ユーザーID
     * @return bool true（削除成功） or false（削除失敗：ユーザーIDが存在しない）
     */
    public function deleteUser(int $p_id): bool
    {
        // ユーザーIDが存在しない
        if(!isset($this->users[$p_id]))
        {
            return false;
        }

        // ユーザー削除
        unset($this->users[$p_id]);

        return true;
    }

    /**
     * アップロードファイルとメタ情報の保存
     * 
     * @param string $p_type ファイルタイプ（'multipart' or 'chunked'）
     * @param ?array $p_bodies ボディ部
     * @param array|UploadedFileInterface $p_files ファイル情報
     */
    public function createUploadWithMeta(string $p_type, ?array $p_bodies, array|UploadedFileInterface $p_files)
    {
        $base_dir = "./upload/{$p_type}";

        // ベースディレクトリが存在しなければ作成
        if(!is_dir($base_dir))
        {
            mkdir($base_dir, 0777, true);
        }

        // 既存ディレクトリ一覧を取得
        $existing = array_filter(scandir($base_dir), function($item) use ($base_dir)
        {
            return is_dir($base_dir . '/' . $item) && ctype_digit($item);
        });
        $existing_ids = array_map('intval', $existing);

        // 空き番号を探す
        $id = 0;
        while(in_array($id, $existing_ids))
        {
            $id++;
        }

        // 新しいディレクトリを作成
        $new_dir = $base_dir . '/' . $id;
        mkdir($new_dir);

        if($p_type === 'chunked')
        {
            $p_files->moveTo($new_dir . '/' . $p_files->getClientFilename());
            $res_body = [
                'id' => $id,
                'mime' => $p_files->getClientMediaType(),
                'size' => $p_files->getSize(),
                'filename' => $p_files->getClientFilename()
            ];
        }
        else
        {
            $p_files[0]->moveTo($new_dir . '/' . $p_files[0]->getClientFilename());
            $res_body = [
                'id' => $id,
                'description' => $p_bodies['description'],
                'mime' => $p_files[0]->getClientMediaType(),
                'size' => $p_files[0]->getSize(),
                'filename' => $p_files[0]->getClientFilename()
            ];
        }

        // JSONファイルを保存
        $json_path = $new_dir . '/meta.json';
        file_put_contents($json_path, json_encode($res_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $res_body;
    }

    /**
     * アップロードファイルのメタ情報の取得
     * 
     * @param string $p_type ファイルタイプ（'multipart' or 'chanked'）
     * @return array メタ情報リスト
     */
    public function getUploadMeta(string $p_type): array
    {
        $base_dir = "./upload/{$p_type}";

        $meta_list = [];

        // ベースディレクトリが存在しなければ終了
        if(!is_dir($base_dir))
        {
            return $meta_list;
        }

        // 既存ディレクトリ一覧を取得
        $existing = array_filter(scandir($base_dir), function($item) use ($base_dir)
        {
            return is_dir($base_dir . '/' . $item) && ctype_digit($item);
        });
        $existing_ids = array_map('intval', $existing);

        // メタ情報取得
        foreach($existing_ids as $id)
        {
            $path = $base_dir . '/' . $id . '/' . 'meta.json';
            $json = file_get_contents($path);
            $meta_list[] = json_decode($json, true);
        }

        return $meta_list;
    }

    /**
     * アップロードファイルの取得
     * 
     * @param string $p_type ファイルタイプ（'multipart' or 'chanked'）
     * @param int $p_id ファイルID
     * @return ?string ファイル or null（存在しないID）
     */
    public function getUploadFile(string $p_type, int $p_id): ?string
    {
        $base_dir = "./upload/{$p_type}/{$p_id}";

        // ベースディレクトリが存在しなければ終了
        if(!is_dir($base_dir))
        {
            return false;
        }

        // メタ情報からファイルパスを取得
        $path_meta = $base_dir . '/' . 'meta.json';
        $json = file_get_contents($path_meta);
        $meta = json_decode($json, true);
        $path_image = $base_dir . '/' . $meta['filename'];

        return $path_image;
    }

    /**
     * チャンク転送ストリーム用のデータ取得
     * 
     * @return ?string ストリームデータ or null（存在しない）
     */
    public function getChunkedStream(): ?string
    {
        $idx = 0;
        $tmp = $this->getTempBuff(['chunked_stream']);
        if($tmp === null || (isset($tmp) && $tmp['chunked_stream'] === null))
        {
            $this->setTempBuff(['chunked_stream' => 0]);
            $this->response()->header('Content-Type', 'text/plain; charset=utf-8');
        }
        else
        {
            $idx = $tmp['chunked_stream'] + 1;
        }
        $dat = null;
        if(isset($this->chunked_stream[$idx]))
        {
            $dat = $this->chunked_stream[$idx];
            $this->setTempBuff(['chunked_stream' => $idx]);
        }

        return $dat;
    }

    /**
     * SSE転送ストリーム用のデータ取得
     * 
     * @param ?int $p_id イベントID（nullの場合はセッション情報から取得）
     * @return ?array ストリームデータ（['id' => <イベントID>, 'data' => <実データ>]） or null（存在しない）
     */
    public function getSseStream(?int $p_id = null): ?array
    {
        $idx = 0;
        if($p_id !== null)
        {
            $idx = $p_id;
        }
        $tmp = $this->getTempBuff(['sse_stream']);
        if($tmp === null || (isset($tmp) && $tmp['sse_stream'] === null))
        {
            $this->setTempBuff(['sse_stream' => $idx]);
            $this->response()->header('Content-Type', 'text/plain; charset=utf-8');
        }
        else
        {
            $idx = $tmp['sse_stream'] + 1;
        }
        $dat = null;
        if(isset($this->sse_stream[$idx]))
        {
            $dat = $this->sse_stream[$idx];
            $this->setTempBuff(['sse_stream' => $idx]);
        }

        $ret = null;
        if($dat !== null)
        {
            $ret = ['id' => $idx, 'data' => $dat];
        }

        return $ret;
    }

}
