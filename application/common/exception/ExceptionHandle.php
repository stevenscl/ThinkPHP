<?php
/**
 * Created by PhpStorm.
 * User: steven
 * Date: 2017/12/6
 * Time: 16:42
 */

namespace app\common\exception;


use Exception;
use think\Config;
use think\exception\Handle;
use think\Log;
use think\Request;

class ExceptionHandle extends Handle
{
    private $code;
    private $msg;
    private $errorCode;

    public function render(Exception $e)
    {
        $request = Request::instance();
        if ($e instanceof BaseException) {
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {
            if (Config::get('app_debug')) {
                return parent::render($e);
            } else {
                $this->code = 500;
                $this->msg = '抱歉,系统出错啦-.-!';
                $this->errorCode = 999;
                $this->recordErrorLog($e, $request->url());
            }
        }
        $result = [
            'msg' => $this->msg,
            'errorCode' => $this->errorCode,
            'requestUrl' => $request->url()
        ];
        return json($result, $this->code);
    }

    private function recordErrorLog(Exception $e ,$requestUrl)
    {
        Log::init([
            'type' => 'File',
            'path' => LOG_PATH . 'error/',
            'level' => ['error']
        ]);
        Log::record(['requestUrl' => $requestUrl, 'info' => $e->getMessage()],'error');
    }
}