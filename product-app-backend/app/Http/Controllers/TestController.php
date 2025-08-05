<?php

namespace App\Http\Controllers;

class TestController
{
    public function index()
    {
        return response()->json([
            'message' => 'Test controller works without extending base Controller!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
