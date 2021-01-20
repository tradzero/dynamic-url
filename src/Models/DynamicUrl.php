<?php

namespace Tradzero\DynamicUrl\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicUrl extends Model
{
    protected $guarded = [];
    
    public function getAvailableEndpoint()
    {
        $result = self::where('enable', true)
            ->where('available', true)
            ->get();
        $urls = $result->pluck('url');
        return $urls;
    }
}
