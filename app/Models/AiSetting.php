<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Runtime AI configuration overrides persisted to the database.
 *
 * Stored as a flat key/value store. Keys mirror the config paths they
 * override (e.g. "ai.provider", "ai.models.default") so the running
 * configuration can be changed from the Admin AI Settings page without
 * rewriting .env — which previously risked interrupting a running server
 * mid-write. When a key is absent the regular config() value applies.
 */
class AiSetting extends Model
{
    protected $table = 'ai_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];
}