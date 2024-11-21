<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(title="Task-Project", version="1.0")
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function dateFromatter($dateTime,$format){
        $date=date('Y-m-d H:i:s',strtotime($dateTime));
        if($format){
            $date=date($format,strtotime($dateTime));
        }
        return $date;
    }

    
}
