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
			CONCAT(hap.cpf, hav.placa) external_id,
            CONCAT(hav.id, ' / ', hap.nome, ' / ', hav.placa, ' / ', IF(habv.id_beneficio IN (118,122,130,174,234), 'PROTEC', 'LOCALIZO'), ' / ', has.nome) `name`,
            CONCAT(IFNULL(hap.logradouro, ''), ' ', IFNULL(hap.numero, ''), ', ', IFNULL(hap.bairro, ''), ' - ', IFNULL(hmuc.cidade, ''), ' ', IFNULL(hmus.uf, '')) address,
				CONCAT(DATE_FORMAT(hav.create_at, '%d/%m/%y'), ' / ', IFNULL(haf.modelo, ''), ' / ', IFNULL(hac.nome, ''), ' ', IFNULL(hac.telefone, hac.telefone)) `description`,
            IFNULL(hap.tel_celular, '00000000000') phone_number,
            DATE_FORMAT(hav.create_at, '%Y-%m-%dT%H:%i') task_date,
            CASE
            	WHEN has.id IN (1, 5) THEN 'instalação'
					WHEN has.id = 4 THEN 'troca_titularidade'
					ELSE 'remoção'
				END task_type,
            hap.cpf,
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
            LEFT JOIN hbrd_adm_fipe haf ON hav.codigo_fipe = haf.codigofipe
            LEFT JOIN hbrd_adm_consultant hac on hac.id = hav.id_consultor
            WHERE has.id IN (1, 3, 4, 5, 7, 8, 9, 10, 16)
            AND hab.id IN (31, 118, 119, 122, 123, 130, 181)
            AND hav.dt_contrato > '2025-02-01'

            ");
        } catch (\Exception $e) {
            Log::error('Error on getVehiclesForAuvoTrackingInSolidy', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public static function getVehiclesForAuvoTrackingInMotoclub(): array
    {
        try {
            return DB::connection('ileva_motoclub')
                ->select("
                SELECT DISTINCT hav.id,
			CONCAT('mc', hap.cpf, hav.placa) external_id,
            CONCAT('mc', hav.id, ' / ', hap.nome, ' / ', hav.placa, ' / ', IF(habv.id_beneficio IN (118,122,130,174,234), 'PROTEC', 'LOCALIZO'), ' / ', has.nome) `name`,
            CONCAT(IFNULL(hap.logradouro, ''), ' ', IFNULL(hap.numero, ''), ', ', IFNULL(hap.bairro, ''), ' - ', IFNULL(hmuc.cidade, ''), ' ', IFNULL(hmus.uf, '')) address,
			CONCAT(DATE_FORMAT(hav.create_at, '%d/%m/%y'), ' / ', IFNULL(haf.modelo, ''), ' / ', IFNULL(hac.nome, ''), ' ', IFNULL(hac.telefone, hac.telefone)) `description`,
            IFNULL(hap.tel_celular, '00000000000') phone_number,
            DATE_FORMAT(hav.create_at, '%Y-%m-%dT%H:%i') task_date,
            CASE
            	WHEN has.id IN (1, 5) THEN 'instalação'
					WHEN has.id = 4 THEN 'troca_titularidade'
					ELSE 'remoção'
				END task_type,
            hap.cpf,
            'localizo_motoclub' team
            FROM hbrd_asc_veiculo hav
            LEFT JOIN hbrd_asc_beneficio_veiculo habv on hav.id = habv.id_veiculo
            LEFT JOIN hbrd_asc_associado haa on haa.id = hav.id_associado
            LEFT JOIN hbrd_asc_pessoa hap on hap.id = haa.id_pessoa
           	LEFT JOIN hbrd_adm_benefit hab on hab.id = habv.id_beneficio
            LEFT JOIN hbrd_adm_plan_beneficio hapb ON  hapb.id_plano = hab.id
            LEFT JOIN hbrd_main_util_city hmuc ON hmuc.id = hap.id_cidade
            LEFT JOIN hbrd_main_util_state hmus ON hmus.id = hmuc.id_estado
            LEFT JOIN hbrd_asc_situacao has ON hav.id_situacao = has.id
            LEFT JOIN hbrd_adm_plan_item hapi ON hav.id_plan_item = hapi.id
            LEFT JOIN hbrd_adm_fipe haf ON hav.codigo_fipe = haf.codigofipe
            LEFT JOIN hbrd_adm_consultant hac on hac.id = hav.id_consultor
            WHERE hab.id IN (48, 121, 126, 130, 137, 138, 141, 169, 180)
            AND hav.dt_contrato >= '2025-02-01';
            ");
        } catch (\Exception $e) {
            Log::error('Error on getVehiclesForAuvoTrackingInSolidy', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
