<?php
namespace Obuchmann\OdooJsonRpc\Exceptions;
use Exception;
class LaravelOdooException extends Exception
{   
    public function render($request)
    {       
        return response()->json(["success" => false, "message" => $this->getMessage() ]);       
    }
}