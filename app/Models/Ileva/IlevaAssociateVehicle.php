<?php

namespace App\Models\Ileva;

use App\Models\Call;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IlevaAssociateVehicle extends Model
{
    use HasFactory;

    protected $connection = 'ileva';
    protected $table = 'hbrd_asc_veiculo';

    protected $guarded = [];

    public function ilevaAssociate()
    {
        return $this->belongsTo(IlevaAssociate::class, 'id_associado');
    }

    public function ilevaModel(): HasOne
    {
        return $this->hasOne(IlevaAssociateVehicleModel::class, 'id', 'id_modelo');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class, 'ileva_associate_vehicle_id');
    }

    public function ilevaColor(): HasOne
    {
        return $this->hasOne(IlevaAssociateVehicleColor::class, 'id', 'id_cor');
    }

    public static function getVehiclesForAuvoTrackingInSolidy(): array
    {
        try {
            return DB::connection('ileva')
                ->select("
            SELECT DISTINCT hav.id,
			CONCAT(hap.cpf, '-', hav.placa) external_id,
            CONCAT(hav.id, ' / ', hap.nome, ' / ', hav.placa, ' / ', IF(habv.id_beneficio IN (118,122,130,174,234), 'PROTEC', 'LOCALIZO'), ' / ', has.nome) `name`,
            CONCAT(IFNULL(hap.logradouro, ''), ' ', IFNULL(hap.numero, ''), ', ', IFNULL(hap.bairro, ''), ' - ', IFNULL(hmuc.cidade, ''), ' ', IFNULL(hmus.uf, '')) address,
			DATE_FORMAT(hav.dt_contrato, '%d/%m/%y') `description`,
            IFNULL(hap.tel_celular, '00000000000') phone_number,
            CONCAT(DATE_FORMAT(hav.dt_contrato, '%Y-%m-%d'), 'T01:01') task_date,
            CASE
            	WHEN has.id IN (1, 5) THEN 'instalação'
					WHEN has.id = 4 THEN 'troca_titularidade'
					ELSE 'remoção'
				END task_type,
            IF(habv.id_beneficio IN (118,122,130,174,234), 'protec', 'localizo') team
            FROM hbrd_asc_veiculo hav
            INNER JOIN hbrd_asc_beneficio_veiculo habv on hav.id = habv.id_veiculo
            INNER JOIN hbrd_asc_associado haa on haa.id = hav.id_associado
            INNER JOIN hbrd_asc_pessoa hap on hap.id = haa.id_pessoa
            INNER JOIN hbrd_adm_benefit hab on hab.id = habv.id_beneficio
            LEFT JOIN hbrd_adm_plan_beneficio hapb ON  hapb.id_plano = hab.id
            LEFT JOIN hbrd_main_util_city hmuc ON hmuc.id = hap.id_cidade
            LEFT JOIN hbrd_main_util_state hmus ON hmus.id = hmuc.id_estado
            INNER JOIN hbrd_asc_situacao has ON hav.id_situacao = has.id
            LEFT JOIN hbrd_adm_plan_item hapi ON hav.id_plan_item = hapi.id
            WHERE hab.id IN (118, 122, 130, 174, 234, 181, 123, 119, 31)
            AND has.id IN (1, 4, 5, 7, 8, 9, 16)
            AND hapi.id_plan IN (209, 211, 212, 213, 214, 216, 217, 218, 219, 220, 222, 223, 224, 225, 227, 228, 229, 232, 237, 238, 240, 241, 243, 244)
            AND YEAR(hav.dt_contrato) >= 2024;
            ");
        } catch (\Exception $e) {
            Log::error('Error on getVehiclesForAuvoTrackingInSolidy', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
