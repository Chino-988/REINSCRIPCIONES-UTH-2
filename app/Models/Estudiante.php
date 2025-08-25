<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;

class Estudiante extends Model
{
    protected $table = 'estudiantes';
    protected $guarded = [];

    protected $casts = [
        'validado_en'    => 'datetime',
        'validado_datos' => 'boolean',

        // Arrays JSON que guardas desde el perfil:
        'telefonos'  => 'array',
        'correos'    => 'array',
        'domicilios' => 'array',
    ];

    /* ================= Relaciones ================= */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    /** Última reinscripción asociada (por ID más alto). */
    public function reinscripcion(): HasOne
    {
        return $this->hasOne(Reinscripcion::class)->latestOfMany('id');
    }

    /**
     * RELACIÓN 'condicion' usada por Admin (eager load con with('condicion')).
     * Detecta esquema disponible para no romper (FK numérica o catálogo por clave).
     */
    public function condicion(): BelongsTo
    {
        if (Schema::hasColumn($this->getTable(), 'condicion_id') && class_exists(\App\Models\Condicion::class)) {
            return $this->belongsTo(\App\Models\Condicion::class, 'condicion_id')->withDefault(['nombre' => '—']);
        }

        if (Schema::hasColumn($this->getTable(), 'condicion_funcional') && class_exists(\App\Models\CatalogoCondicion::class)) {
            return $this->belongsTo(\App\Models\CatalogoCondicion::class, 'condicion_funcional', 'clave')
                        ->withDefault(['nombre' => '—']);
        }

        // Fallback seguro: permite with('condicion') sin tronar aunque no haya esquema
        return $this->belongsTo(self::class, 'id', 'id')->whereRaw('1=0');
    }

    /**
     * ✅ RELACIÓN HasOne real para los checkboxes del formulario.
     * Esto es lo que usa tu PerfilController (propiedad y firstOrNew).
     */
    public function condicionesFuncionales(): HasOne
    {
        return $this->hasOne(CondicionFuncional::class);
    }

    /* ================= Catálogos / Utilidades ================= */

    public static function catalogoCondicionesFuncionales(): array
    {
        return [
            'NINGUNA'     => 'Ninguna',
            'VISUAL'      => 'Discapacidad visual',
            'AUDITIVA'    => 'Discapacidad auditiva',
            'MOTRIZ'      => 'Discapacidad motriz',
            'INTELECTUAL' => 'Discapacidad intelectual',
            'PSICOSOCIAL' => 'Discapacidad psicosocial',
            'CRONICA'     => 'Enfermedad crónica',
            'EMBARAZO'    => 'Embarazo',
            'OTRA'        => 'Otra',
        ];
    }

    public function condicionFuncionalActual(): ?string
    {
        $tabla = $this->getTable();
        foreach (['condicion_funcional','condiciones_funcionales','tipo_discapacidad'] as $col) {
            if (Schema::hasColumn($tabla, $col)) {
                $val = $this->getAttribute($col);
                return $val !== null ? (string) $val : null;
            }
        }
        return null;
    }

    public function condicionFuncionalActualLabel(): ?string
    {
        $cat = self::catalogoCondicionesFuncionales();
        $key = $this->condicionFuncionalActual();
        return $key && isset($cat[$key]) ? $cat[$key] : null;
    }

    public function getCondicionFuncionalLabelAttribute(): ?string
    {
        return $this->condicionFuncionalActualLabel();
    }
}
