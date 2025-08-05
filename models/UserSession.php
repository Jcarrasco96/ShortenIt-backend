<?php

namespace ShortenIt\models;

use SimpleApiRest\rest\Model;

class UserSession extends Model
{

    public string $id;
    public string $user_id;
    public string $refresh_hash;
    public string $user_agent;
    public string $ip_address;
    public string $created_at;
    public string $expires_at;
    public bool $is_active;

    protected const PROPS = ['id', 'user_id', 'refresh_hash', 'user_agent', 'ip_address', 'created_at', 'expires_at', 'is_active'];

}