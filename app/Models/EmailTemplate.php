<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'subject',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the email template.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Render the template with the given data.
     */
    public function render(array $data): string
    {
        $content = $this->content;
        foreach ($data as $key => $value) {
            $content = str_replace('{{ '.$key.' }}', (string) $value, $content);
        }

        return $content;
    }

    /**
     * Render the subject with the given data.
     */
    public function renderSubject(array $data): string
    {
        $subject = $this->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace('{{ '.$key.' }}', (string) $value, $subject);
        }

        return $subject;
    }
}
