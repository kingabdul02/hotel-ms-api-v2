<?php

namespace App\Traits;

use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Api Responser Trait
|--------------------------------------------------------------------------
|
| This trait will be used for any response we sent to clients.
|
*/

trait JsonResponse
{
    /**
     * Return a success JSON response.
     *
     * @param  array|string  $data
     * @param  string  $message
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    protected function success($data = null, int $status = 200)
    {
        $response = [
            "status" => "success"
        ];

        !is_null($data) && $response['data'] = $data;

        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    protected function error($message, int $status = 400)
    {
        $response = [
            "status" => "error",
            "message" => $message
        ];

        return response()->json($response, $status);
    }
}
