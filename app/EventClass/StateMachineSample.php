<?php
/**
 * ステートマシン登録クラスのファイル
 * 
 */

namespace App\EventClass;

use App\CommandUnits\CommandForStateMachine;
use App\ContextClass\ContextForSample;


/**
 * ステートマシン登録クラス
 * 
 * CommandForStateMachineクラスをオーバーライドして利用する
 */
class StateMachineSample extends CommandForStateMachine
{
    /**
     * ユーザー情報取得
     * 
     */
    protected function getUsers()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
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

                    return null;
                }
            ]
        ];
    }

    /**
     * 新規ユーザー作成
     * 
     */
    protected function postUser()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    // ボディ部（name:名前、email:Eメール）の取得
                    $body = $p_param->request()->body();

                    // ユーザーの追加
                    $add_user = $p_param->addUser($body['name'], $body['email']);

                    $p_param->response()->status(200)->json($add_user);

                    return null;
                }
            ]
        ];
    }

    /**
     * ユーザー全体更新
     * 
     */
    protected function putUser()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
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
                            $error_param = 'id';
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

                    return null;
                }
            ]
        ];
    }

    /**
     * ユーザー部分更新
     * 
     */
    protected function patchUser()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
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

                    return null;
                }
            ]
        ];
    }

    /**
     * ユーザー削除
     * 
     */
    protected function deleteUser()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
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

                    return null;
                }
            ]
        ];
    }

    /**
     * ICOファイルの取得
     * 
     */
    protected function getIco()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    $root = config('parameter-sample.document_root');
                    $file = $p_param->request()->params('file');
                    $path = "{$root}/{$file}";
                    $p_param->response()->file($path);

                    return null;
                }
            ]
        ];
    }

    /**
     * HTMLファイルの取得
     * 
     */
    protected function getHtml()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    $root = config('parameter-sample.document_root');
                    $path = $root.$p_param->request()->path();
                    $p_param->response()->html($path);

                    return null;
                }
            ]
        ];
    }

    /**
     * Javascriptファイルの取得
     * 
     */
    protected function getJavascript()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    $root = config('parameter-sample.document_root');
                    $file = $p_param->request()->params('file');
                    $path = "{$root}/js/{$file}";
                    $p_param->response()->javascript($path);

                    return null;
                }
            ]
        ];
    }

    /**
     * CSSファイルの取得
     * 
     */
    protected function getCss()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    $root = config('parameter-sample.document_root');
                    $file = $p_param->request()->params('file');
                    $path = "{$root}/css/{$file}";
                    $p_param->response()->css($path);

                    return null;
                }
            ]
        ];
    }

    /**
     * ファイルアップロード（multipart）
     * 
     */
    protected function fileUploadByMultipart()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    // フォームボディの取得
                    $body = $p_param->request()->body();

                    // ファイルの取得
                    $files = $p_param->request()->files();

                    // ファイル本体とファイル情報の格納
                    $res_body = $p_param->createUploadWithMeta('multipart', $body, $files);

                    // レスポンス送信
                    $p_param->response()->status(201)->json($res_body);

                    return null;
                }
            ]
        ];
    }

    /**
     * アップロードファイル情報の取得（multipart）
     * 
     */
    protected function getFilesByMultipart()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    // メタ情報リストの取得
                    $list = $p_param->getUploadMeta('multipart');
                    $p_param->response()->status(200)->json($list);

                    return null;
                }
            ]
        ];
    }

    /**
     * アップロードファイルの取得（multipart）
     * 
     */
    protected function getFileByMultipart()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    // IDの取得
                    $id = $p_param->request()->params('id');

                    // ファイルパスの取得
                    $path = $p_param->getUploadFile('multipart', $id);

                    $p_param->response()->status(200)->file($path);

                    return null;
                }
            ]
        ];
    }

    /**
     * ファイルダウンロード（multipart）
     * 
     */
    protected function downloadFileByMultipart()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    // IDの取得
                    $id = $p_param->request()->params('id');

                    // ファイル情報の取得
                    $path = $p_param->getUploadFile('multipart', $id);
                    $filename = basename($path);

                    $p_param->response()->status(200)->download($path, $filename);

                    return null;
                }
            ]
        ];
    }

    /**
     * ファイルアップロード（chunked）
     * 
     */
    protected function fileUploadByChunked()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    // ファイルの取得
                    $file = $p_param->request()->asFile();

                    // ファイル本体とファイル情報の格納
                    $res_body = $p_param->createUploadWithMeta('chunked', null, $file);

                    // レスポンス送信
                    $p_param->response()->status(201)->json($res_body);

                    return null;
                }
            ]
        ];
    }

    /**
     * アップロードファイル情報の取得（chunked）
     * 
     */
    protected function getFilesByChunked()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    // メタ情報リストの取得
                    $list = $p_param->getUploadMeta('chunked');
                    $p_param->response()->status(200)->json($list);

                    return null;
                }
            ]
        ];
    }

    /**
     * アップロードファイルの取得（chunked）
     * 
     */
    protected function getFileByChunked()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    // IDの取得
                    $id = $p_param->request()->params('id');

                    // ファイルパスの取得
                    $path = $p_param->getUploadFile('chunked', $id);

                    $p_param->response()->status(200)->file($path);

                    return null;
                }
            ]
        ];
    }

    /**
     * ファイルダウンロード（chunked）
     * 
     */
    protected function downloadFileByChunked()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    // IDの取得
                    $id = $p_param->request()->params('id');

                    // ファイル情報の取得
                    $path = $p_param->getUploadFile('chunked', $id);
                    $filename = basename($path);

                    $p_param->response()->status(200)->download($path, $filename);

                    return null;
                }
            ]
        ];
    }

    /**
     * チャンク転送ストリーム（chunked）
     * 
     */
    protected function getStreamByChunked()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    // 次のデータを取得
                    $dat = $p_param->getChunkedStream();
                    if($dat === null)
                    {
                        // 送信終了
                        $p_param->response()->end();
                        return null;
                    }
                    $p_param->response()->chunked($dat);

                    return 'second';
                }
            ]
            ,
            [
                'status' => 'second',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    $p_param->setTimeout(5000);

                    // 次のデータを取得
                    $dat = $p_param->getChunkedStream();
                    if($dat === null)
                    {
                        // 送信終了
                        $p_param->response()->end();
                        return null;
                    }
                    $p_param->response()->chunked($dat);

                    return 'second';
                }
            ]
        ];
    }

    /**
     * SSE転送ストリーム
     * 
     */
    protected function getStreamBySse()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    $id = $p_param->request()->header('last-event-id');
                    if($id === null)
                    {
                        $id = 0;
                    }

                    // 次のデータを取得
                    $dat = $p_param->getSseStream($id);
                    if($dat === null)
                    {
                        // 送信終了
                        $p_param->response()->event('done', p_event: 'end');
                        $p_param->response()->end();
                        return null;
                    }
                    $p_param->response()->event($dat['data'], p_id: $dat['id']);

                    return 'second';
                }
            ]
            ,
            [
                'status' => 'second',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    $p_param->setTimeout(5000);

                    // 次のデータを取得
                    $dat = $p_param->getSseStream();
                    if($dat === null)
                    {
                        // 送信終了
                        $p_param->response()->event('done', p_event: 'end');
                        $p_param->response()->end();
                        return null;
                    }
                    $p_param->response()->event($dat['data'], p_id: $dat['id']);

                    return 'second';
                }
            ]
        ];
    }

    /**
     * Range指定のデータ取得（バイナリ形式）
     * 
     */
    protected function getBinaryByRange()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    $p_param->response()->rangeBinary('AAAAAAAAAABBBBBBBBBBCCCCCCCCCCDDDDDDDDDDEEEEEEEEEE');

                    return null;
                }
            ]
        ];
    }

    /**
     * Range指定のデータ取得（ファイル形式）
     * 
     */
    protected function getFileByRange()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    $p_param->response()->rangeFile('./upload/range/numbers.txt');

                    return null;
                }
            ]
        ];
    }

    /**
     * Expectヘッダ受信
     * 
     */
    protected function expectHeader()
    {
        return [
            [
                'status' => 'start',
                'unit' => function(ContextForSample $p_param): ?string
                {
                    $p_param->response()->status(100)->send();

                    return null;
                }
            ]
        ];
    }
}
