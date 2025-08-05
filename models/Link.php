<?php

namespace ShortenIt\models;

use SimpleApiRest\rest\Model;

class Link extends Model
{

    public string $id;
    public string $original_url;
    public string $short_code;
    public int $access_count;
    public string $created_at;

    protected const PROPS = ['id', 'original_url', 'short_code', 'access_count', 'created_at'];

}