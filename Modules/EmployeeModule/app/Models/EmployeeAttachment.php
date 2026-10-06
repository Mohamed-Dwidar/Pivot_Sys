<?php

namespace Modules\EmployeeModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

// employee file (ID, contract, ...), saved on the private disk: storage/app/private/employees/{employee_id}/
class EmployeeAttachment extends Model
{
    protected $fillable = ['employee_id', 'attach_name', 'attach_label'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getPathAttribute(): string
    {
        return 'employees/' . $this->employee_id . '/' . $this->attach_name;
    }

    // download name: the label (Arabic kept) + the file extension
    public function getDownloadNameAttribute(): string
    {
        $extension = pathinfo($this->attach_name, PATHINFO_EXTENSION);
        $name = Str::slug((string) $this->attach_label, '-', null) ?: 'attachment-' . $this->id;
        return $name . ($extension ? '.' . $extension : '');
    }
}
