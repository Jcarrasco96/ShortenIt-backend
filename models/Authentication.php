<?php

namespace ShortenIt\models;

use SimpleApiRest\rest\Model;

class Authentication extends Model
{

	public string $id;
	public string $item_name;
	public string $user_id;
	public string $created_at;

    protected const PROPS = ['id', 'item_name', 'user_id', 'created_at'];

}
