<?php
/**
 * ハンドラ登録クラスのファイル
 * 
 */

namespace App\EventClass;

use App\ContextClass\ContextForSample;
use App\CommandUnits\CommandForHandler;


/**
 * ハンドラ登録クラス
 * 
 * CommandForHandlerクラスをオーバーライドして利用する
 */
class EventHandlerSample extends CommandForHandler
{
    /**
     * ユーザー情報取得
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getUsers(ContextForSample $p_param)
    {
        // ユーザーIDの取得
        $id = $p_param->request()->params('id');

        // ユーザーリストの取得
        $body = $p_param->getUserList($id);

        if(count($body))
        // レスポンスデータがある場合
        {
            $p_param->response()->status(200)->json($body);
        }
        else
        // レスポンスデータがない（該当IDが存在しない）場合
        {
            $p_param->response()->status(400);
            $body = [
                'error' => $p_param->response()->getReason(),
                'message' => __('error.MESSAGE_BODY_NOT_FOUND_PARAM', ['param' => 'id']),
                'status' => 400
            ];
            $p_param->response()->json($body);
        }
    }

    /**
     * 新規ユーザー作成
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function postUser(ContextForSample $p_param)
    {
        // ボディ部（name:名前、email:Eメール）の取得
        $body = $p_param->request()->body();

        // ユーザーの追加
        $add_user = $p_param->addUser($body['name'], $body['email']);

        $p_param->response()->status(201)->json($add_user);
    }

    /**
     * ユーザー全体更新
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function putUser(ContextForSample $p_param)
    {
        $result = false;
        $error_param = [];
        while(true)
        {
            // ユーザーIDの取得
            $id = $p_param->request()->params('id');

            // ID指定がない場合
            if($id === null)
            {
                $error_param = 'id';
                break;
            }

            // ボディ部（name:名前、email:Eメール）の取得
            $body = $p_param->request()->body();

            // name指定がない場合
            if(!isset($body['name'])
            || !strlen($body['name']))
            {
                $error_param[] = 'name';
            }

            // email指定がない場合
            if(!isset($body['email'])
            || !strlen($body['email']))
            {
                $error_param[] = 'email';
            }

            // いずれかのパラメータがない場合は抜ける
            if(count($error_param))
            {
                break;
            }

            // ユーザー全体更新
            $put_user = $p_param->updateUser($id, $body['name'], $body['email']);

            // レスポンスデータがない（ユーザーIDが存在しない）場合
            if($put_user === null)
            {
                $error_param[] = 'id';
                break;
            }

            $result = true;
            break;
        }

        // レスポンス送信
        if($result)
        {
            $p_param->response()->status(200)->json($put_user);
        }
        else
        {
            $p_param->response()->status(400);
            $body = [
                'error' => $p_param->response()->getReason(),
                'message' => __('error.MESSAGE_BODY_NOT_FOUND_PARAM', ['param' => implode(', ', $error_param)]),
                'status' => 400
            ];
            $p_param->response()->json($body);
        }
    }

    /**
     * ユーザー部分更新
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function patchUser(ContextForSample $p_param)
    {
        $result = false;
        $updated_user = [];
        $error_param = '';
        while(true)
        {
            // ユーザーIDの取得
            $id = $p_param->request()->params('id');

            // ID指定がない場合
            if($id === null)
            {
                $error_param = 'id';
                break;
            }

            // ボディ部（name:名前、email:Eメール）の取得
            $body = $p_param->request()->body();

            // nameの設定
            $name = null;
            if(isset($body['name']))
            {
                $name = $body['name'];
            }

            // emailの設定
            $email = null;
            if(isset($body['email']))
            {
                $email = $body['email'];
            }

            // 変更項目がない場合
            if($name === null && $email === null)
            {
                $error_param = 'name, email';
                break;
            }

            // ユーザー全体更新
            $updated_user = $p_param->updateUser($id, $name, $email);

            // レスポンスデータがない（ユーザーIDが存在しない）場合
            if($updated_user === null)
            {
                $error_param = 'id';
                break;
            }

            $result = true;
            break;
        }

        // レスポンス送信
        if($result)
        {
            $p_param->response()->status(200)->json($updated_user);
        }
        else
        {
            $p_param->response()->status(400);
            $body = [
                'error' => $p_param->response()->getReason(),
                'message' => __('error.MESSAGE_BODY_NOT_FOUND_PARAM', ['param' => $error_param]),
                'status' => 400
            ];
            $p_param->response()->json($body);
        }
    }

    /**
     * ユーザー削除
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function deleteUser(ContextForSample $p_param)
    {
        $result = false;
        while(true)
        {
            // ユーザーIDの取得
            $id = $p_param->request()->params('id');

            // ID指定がない場合
            if($id === null)
            {
                break;
            }

            // ユーザーの削除
            $ret = $p_param->deleteUser($id);

            // ユーザーIDが存在しない場合
            if($ret === false)
            {
                break;
            }

            $result = true;
            break;
        }

        // レスポンス送信
        if($result)
        {
            $p_param->response()->status(204)->send();
        }
        else
        {
            $p_param->response()->status(400);
            $body = [
                'error' => $p_param->response()->getReason(),
                'message' => __('error.MESSAGE_BODY_NOT_FOUND_PARAM', ['param' => 'id']),
                'status' => 400
            ];
            $p_param->response()->json($body);
        }
    }

    /**
     * ICOファイルの取得
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getIco(ContextForSample $p_param)
    {
        $root = config('parameter-sample.document_root');
        $file = $p_param->request()->params('file');
        $path = "{$root}/{$file}";
        $p_param->response()->file($path);
    }

    /**
     * HTMLファイルの取得
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getHtml(ContextForSample $p_param)
    {
        $root = config('parameter-sample.document_root');
        $path = $root.$p_param->request()->path();
        $p_param->response()->html($path);
    }

    /**
     * Javascriptファイルの取得
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getJavascript(ContextForSample $p_param)
    {
        $root = config('parameter-sample.document_root');
        $file = $p_param->request()->params('file');
        $path = "{$root}/js/{$file}";
        $p_param->response()->javascript($path);
    }

    /**
     * CSSファイルの取得
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getCss(ContextForSample $p_param)
    {
        $root = config('parameter-sample.document_root');
        $file = $p_param->request()->params('file');
        $path = "{$root}/css/{$file}";
        $p_param->response()->css($path);
    }

    /**
     * ファイルアップロード（multipart）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function fileUploadByMultipart(ContextForSample $p_param)
    {
        // フォームボディの取得
        $body = $p_param->request()->body();

        // ファイルの取得
        $files = $p_param->request()->files();

        // ファイル本体とファイル情報の格納
        $res_body = $p_param->createUploadWithMeta('multipart', $body, $files);

        // レスポンス送信
        $p_param->response()->status(201)->json($res_body);
    }

    /**
     * アップロードファイル情報の取得（multipart）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getFilesByMultipart(ContextForSample $p_param)
    {
        // メタ情報リストの取得
        $list = $p_param->getUploadMeta('multipart');
        $p_param->response()->status(200)->json($list);
    }

    /**
     * アップロードファイルの取得（multipart）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getFileByMultipart(ContextForSample $p_param)
    {
        // IDの取得
        $id = $p_param->request()->params('id');

        // ファイルパスの取得
        $path = $p_param->getUploadFile('multipart', $id);

        $p_param->response()->status(200)->file($path);
    }

    /**
     * ファイルダウンロード（multipart）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function downloadFileByMultipart(ContextForSample $p_param)
    {
        // IDの取得
        $id = $p_param->request()->params('id');

        // ファイル情報の取得
        $path = $p_param->getUploadFile('multipart', $id);
        $filename = basename($path);

        $p_param->response()->status(200)->download($path, $filename);
    }

    /**
     * ファイルアップロード（chunked）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function fileUploadByChunked(ContextForSample $p_param)
    {
        // ファイルの取得
        $file = $p_param->request()->asFile();

        // ファイル本体とファイル情報の格納
        $res_body = $p_param->createUploadWithMeta('chunked', null, $file);

        // レスポンス送信
        $p_param->response()->status(201)->json($res_body);
    }

    /**
     * アップロードファイル情報の取得（chunked）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getFilesByChunked(ContextForSample $p_param)
    {
        // メタ情報リストの取得
        $list = $p_param->getUploadMeta('chunked');
        $p_param->response()->status(200)->json($list);
    }

    /**
     * アップロードファイルの取得（chunked）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getFileByChunked(ContextForSample $p_param)
    {
        // IDの取得
        $id = $p_param->request()->params('id');

        // ファイルパスの取得
        $path = $p_param->getUploadFile('chunked', $id);

        $p_param->response()->status(200)->file($path);
    }

    /**
     * ファイルダウンロード（chunked）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function downloadFileByChunked(ContextForSample $p_param)
    {
        // IDの取得
        $id = $p_param->request()->params('id');

        // ファイル情報の取得
        $path = $p_param->getUploadFile('chunked', $id);
        $filename = basename($path);

        $p_param->response()->status(200)->download($path, $filename);
    }

    /**
     * チャンク転送ストリーム（chunked）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getStreamByChunked(ContextForSample $p_param)
    {
        // データの送信
        while(true)
        {
            // 次のデータを取得
            $dat = $p_param->getChunkedStream();
            if($dat === null)
            {
                break;
            }
            $p_param->response()->chunked($dat);
        }

        // 送信終了
        $p_param->response()->end();
    }

    /**
     * SSE転送ストリーム
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getStreamBySse(ContextForSample $p_param)
    {
        $id = $p_param->request()->header('last-event-id');
        if($id === null)
        {
            $id = 0;
        }

        // データの送信
        while(true)
        {
            // 次のデータを取得
            $dat = $p_param->getSseStream($id);
            if($dat === null)
            {
                break;
            }
            $p_param->response()->event($dat['data'], p_id: $dat['id']);
        }

        // 送信終了
        $p_param->response()->event('done', p_event: 'end');
        $p_param->response()->end();
    }

    /**
     * Range指定のデータ取得（バイナリ形式）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getBinaryByRange(ContextForSample $p_param)
    {
        $p_param->response()->rangeBinary('AAAAAAAAAABBBBBBBBBBCCCCCCCCCCDDDDDDDDDDEEEEEEEEEE');
    }

    /**
     * Range指定のデータ取得（ファイル形式）
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function getFileByRange(ContextForSample $p_param)
    {
        $p_param->response()->rangeFile('./upload/range/numbers.txt');
    }

    /**
     * Expectヘッダ受信
     * 
     * @param ContextForSample $p_param コンテキストパラメータ
     */
    protected function expectHeader(ContextForSample $p_param)
    {
        $p_param->response()->status(100)->send();
    }

}
