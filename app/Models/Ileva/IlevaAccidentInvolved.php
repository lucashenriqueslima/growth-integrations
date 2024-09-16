<?php

namespace App\Models\Ileva;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class IlevaAccidentInvolved extends Model
{
    use HasFactory;

    protected $connection = 'ileva';
    protected $table = 'hbrd_adm_sinister_participant';
    protected $guarded = [];

    public static function getAccidentInvolvedForAuvoToSolidy(): array
    {
        return DB::connection('ileva')
            ->select("
              SELECT DISTINCT
    par.id external_id,
    CONCAT(par.id, ' / ', par.nome, ' / ', par.placa) AS `name`,
    status.id_pai,
    tipe.id_participant,
    status.create_at start_date,
    DATE_FORMAT(status.create_at, '%d/%m/%y') AS dataContrato,
    CONCAT(DATE_FORMAT(status.create_at, '%d/%m/%y'), ' ', DATEDIFF(NOW(), status.create_at), ' dia(s)') AS note,
    par.nome,
    par.placa,
    par.cpf_cnpj AS cpfCnpj,
    has.id AS workshop_id,
    CONCAT(IFNULL(has.nome, ''), ' / ', IFNULL(has.endereco, ''), ' / ', IFNULL(city.cidade, ''), ' - ', IFNULL(state.uf, '')) AS address,
    CONCAT(IFNULL(has.nome, ''), ' / Placa: ', IFNULL(par.placa, ''), ' / Veículo: ', IFNULL(par.modelo_veiculo, '')) AS orientation,
    par.telefone AS phone,
    par.email,
    par.id_sinister,
    status.id_status,
    tipe.id_tipo,
    par.status,
    status.create_at AS dt_criacao,
    par.create_at AS data_criacao,
    has.endereco,
    city.cidade,
    state.uf,
    COALESCE(
        (
            SELECT MIN(status_history.create_at)
            FROM hbrd_adm_sinister_participant_status_history status_history
            WHERE status_history.create_at > status.create_at
              AND status.id_pai = status_history.id_pai
        ),
        status.leave_at
    ) AS data_da_proxima_etapa,
    status.leave_at,
    s.nome,
    city.cidade AS cidade_associado,
    state.uf AS estado_associado

FROM hbrd_adm_sinister_participant_status_history status
LEFT JOIN hbrd_adm_sinister_participant_type_history tipe ON status.id_pai = tipe.id
LEFT JOIN hbrd_adm_sinister_status s ON status.id_status = s.id
LEFT JOIN hbrd_adm_sinister_participant par ON par.id = tipe.id_participant
LEFT JOIN hbrd_adm_sinister_history sh ON sh.id_sinister = par.id_sinister
LEFT JOIN hbrd_adm_sinister_order haso ON haso.id_participant = par.id
LEFT JOIN hbrd_adm_store has ON has.id = haso.id_store
LEFT JOIN hbrd_main_util_city city ON city.id = par.id_cidade
LEFT JOIN hbrd_main_util_state state ON state.id = par.id_estado
WHERE status.id_status = 6
  AND par.status = 'Ativo'
  AND tipe.id_tipo IN ('8', '14')
  AND COALESCE(
        (
            SELECT MIN(status_history.create_at)
            FROM hbrd_adm_sinister_participant_status_history status_history
            WHERE status_history.create_at > status.create_at
              AND status.id_pai = status_history.id_pai
        ),
        status.leave_at
    ) IS NULL
  AND (has.id IS NULL OR has.id != 2670)
  AND (haso.id_store IS NULL OR haso.id_store != 2670)
GROUP BY tipe.id_participant;
        ");
    }

    public static function getAccidentInvolvedForAuvoToMotoclub(): array
    {
        return DB::connection('ileva_motoclub')
            ->select("
              SELECT DISTINCT
    CONCAT ('mc', par.id) external_id,
    CONCAT('mc', par.id, ' / ', par.nome, ' / ', par.placa) AS `name`,
    tipe.id_participant,
    (
        SELECT haso_inner.id
        FROM hbrd_adm_sinister_order haso_inner
        WHERE haso_inner.id_participant = par.id
          AND (haso_inner.id_tipo = 1 OR haso_inner.id_tipo = 13)
        ORDER BY FIELD(haso_inner.id_tipo, 1, 13)
        LIMIT 1
    ) AS id_order,
    DATE_FORMAT(status.create_at, '%d/%m/%y') AS dataContrato,
    CONCAT(DATE_FORMAT(status.create_at, '%d/%m/%y'), ' ', DATEDIFF(NOW(), status.create_at), ' dia(s)') AS note,
    par.nome,
    par.placa,
    par.cpf_cnpj AS cpfCnpj,
    has.id AS workshop_id,
    CONCAT(IFNULL(has.nome, ''), ' / ', IFNULL(has.endereco, ''), ' / ', IFNULL(city.cidade, ''), ' - ', IFNULL(state.uf, '')) AS address,
    CONCAT(IFNULL(has.nome, ''), ' / Placa: ', IFNULL(par.placa, ''), ' / Veículo: ', IFNULL(par.modelo_veiculo, '')) AS orientation,
    par.telefone AS phone,
    par.email,
    par.id_sinister,
    status.id_status,
    tipe.id_tipo,
    par.status,
    status.create_at AS dt_criacao,
    par.create_at AS data_criacao,
    has.endereco,
    city.cidade,
    state.uf,
    COALESCE(
        (
            SELECT MIN(status_history.create_at)
            FROM hbrd_adm_sinister_participant_status_history status_history
            WHERE status_history.create_at > status.create_at
              AND status.id_pai = status_history.id_pai
        ),
        status.leave_at
    ) AS data_da_proxima_etapa,
    status.leave_at,
    s.nome,
    city.cidade AS cidade_associado,
    state.uf AS estado_associado
FROM hbrd_adm_sinister_participant_status_history status
LEFT JOIN hbrd_adm_sinister_participant_type_history tipe ON status.id_pai = tipe.id
LEFT JOIN hbrd_adm_sinister_status s ON status.id_status = s.id
LEFT JOIN hbrd_adm_sinister_participant par ON par.id = tipe.id_participant
LEFT JOIN hbrd_adm_sinister_history sh ON sh.id_sinister = par.id_sinister
LEFT JOIN hbrd_adm_sinister_order haso ON haso.id_participant = par.id
LEFT JOIN hbrd_adm_store has ON has.id = haso.id_store
LEFT JOIN hbrd_main_util_city city ON city.id = par.id_cidade
LEFT JOIN hbrd_main_util_state state ON state.id = par.id_estado
WHERE status.id_status = 6
  AND par.status = 'Ativo'
  AND tipe.id_tipo IN ('8', '14')
  AND COALESCE(
        (
            SELECT MIN(status_history.create_at)
            FROM hbrd_adm_sinister_participant_status_history status_history
            WHERE status_history.create_at > status.create_at
              AND status.id_pai = status_history.id_pai
        ),
        status.leave_at
    ) IS NULL
  AND (has.id IS NULL OR has.id != 2670)
  AND (haso.id_store IS NULL OR haso.id_store != 2670)
GROUP BY tipe.id_participant;
        ");
    }

    public static function getAccidentInvolvedForAuvoToNova(): array
    {
        return DB::connection('ileva_nova')
            ->select("
            SELECT DISTINCT
    CONCAT('nv', par.id) external_id,
    CONCAT('nv', par.id, ' / ', par.nome, ' / ', par.placa) AS `name`,
    status.id_pai,
    tipe.id_participant,
    DATE_FORMAT(status.create_at, '%d/%m/%y') AS dataContrato,
    CONCAT(DATE_FORMAT(status.create_at, '%d/%m/%y'), ' ', DATEDIFF(NOW(), status.create_at), ' dia(s)') AS note,
    par.nome,
    par.placa,
    par.cpf_cnpj AS cpfCnpj,
    has.id AS workshop_id,
    CONCAT(IFNULL(has.nome, ''), ' / ', IFNULL(has.endereco, ''), ' / ', IFNULL(city.cidade, ''), ' - ', IFNULL(state.uf, '')) AS address,
    CONCAT(IFNULL(has.nome, ''), ' / Placa: ', IFNULL(par.placa, ''), ' / Veículo: ', IFNULL(par.modelo_veiculo, '')) AS orientation,
    par.telefone AS phone,
    par.email,
    par.id_sinister,
    status.id_status,
    tipe.id_tipo,
    par.status,
    status.create_at AS dt_criacao,
    par.create_at AS data_criacao,
    has.endereco,
    city.cidade,
    state.uf,
    COALESCE(
        (
            SELECT MIN(status_history.create_at)
            FROM hbrd_adm_sinister_participant_status_history status_history
            WHERE status_history.create_at > status.create_at
              AND status.id_pai = status_history.id_pai
        ),
        status.leave_at
    ) AS data_da_proxima_etapa,
    status.leave_at,
    s.nome,
    city.cidade AS cidade_associado,
    state.uf AS estado_associado
FROM hbrd_adm_sinister_participant_status_history status
LEFT JOIN hbrd_adm_sinister_participant_type_history tipe ON status.id_pai = tipe.id
LEFT JOIN hbrd_adm_sinister_status s ON status.id_status = s.id
LEFT JOIN hbrd_adm_sinister_participant par ON par.id = tipe.id_participant
LEFT JOIN hbrd_adm_sinister_history sh ON sh.id_sinister = par.id_sinister
LEFT JOIN hbrd_adm_sinister_order haso ON haso.id_participant = par.id
LEFT JOIN hbrd_adm_store has ON has.id = haso.id_store
LEFT JOIN hbrd_main_util_city city ON city.id = par.id_cidade
LEFT JOIN hbrd_main_util_state state ON state.id = par.id_estado
WHERE status.id_status = 6
  AND par.status = 'Ativo'
  AND tipe.id_tipo IN ('8', '14')
  AND COALESCE(
        (
            SELECT MIN(status_history.create_at)
            FROM hbrd_adm_sinister_participant_status_history status_history
            WHERE status_history.create_at > status.create_at
              AND status.id_pai = status_history.id_pai
        ),
        status.leave_at
    ) IS NULL
  AND (has.id IS NULL OR has.id != 2670)
  AND (haso.id_store IS NULL OR haso.id_store != 2670)
GROUP BY tipe.id_participant;
        ");
    }

    public static function getAccidentInvolvedForAuvoExpertiseInSolidy(): Collection
    {
        try {
            return collect(
                DB::connection('ileva')
                    ->select("
SELECT DISTINCT
CONCAT('so_', par.id) external_id,
CONCAT('so_', par.id) note,
CONCAT('so_', par.placa) name,
'solidy' customer_group,
CASE
WHEN par.associado = 1 THEN CONCAT('Associado: ', IFNULL(par.nome, ''), ' \\n', 'CPF: ', IFNULL(par.cpf_cnpj, ''), ' \\n', 'Placa: ', IFNULL(par.placa, ''), ' \\n', 'Montadora: ', IFNULL(par.montadora, ''), ' \\n', 'Modelo: ', IFNULL(par.modelo_veiculo, ''), ' \\n', 'Chassi: ', IFNULL(par.chassi, ''), ' \\n', 'Cor: ', IFNULL(par.cor, ''))
ELSE (
        SELECT CONCAT('Associado: ', IFNULL(par2.nome, ''), ' \\n', 'CPF: ', IFNULL(par2.cpf_cnpj, ''), ' \\n', 'Placa: ', IFNULL(par2.placa, ''), ' \\n', 'Montadora: ', IFNULL(par2.montadora, ''), ' \\n', 'Modelo: ', IFNULL(par2.modelo_veiculo, ''), ' \\n', 'Chassi: ', IFNULL(par2.chassi, ''), ' \\n', 'Cor: ', IFNULL(par2.cor, ''))
        FROM hbrd_adm_sinister_participant par2
        WHERE par2.associado = 1
        AND par2.id_sinister = par.id_sinister
        )
END `description`,
CONCAT(IFNULL(hmuc.cidade, ''), ' - ', IFNULL(hmus.estado, '')) address,
DATE_FORMAT(status.create_at, '%Y-%m-%dT%H:%i:%s	') task_date
FROM hbrd_adm_sinister_participant_status_history status
LEFT JOIN hbrd_adm_sinister_participant_type_history tipe ON status.id_pai = tipe.id
LEFT JOIN hbrd_adm_sinister_status s ON status.id_status = s.id
LEFT JOIN hbrd_adm_sinister_participant par ON par.id = tipe.id_participant
LEFT JOIN hbrd_adm_sinister_history sh ON sh.id_sinister = par.id_sinister
LEFT JOIN hbrd_adm_sinister_order haso ON haso.id_participant = par.id
LEFT JOIN hbrd_adm_sinister has ON has.id = par.id_sinister
LEFT JOIN hbrd_main_util_city hmuc ON hmuc.id = par.id_cidade
LEFT JOIN hbrd_main_util_state hmus ON hmuc.id_estado = hmus.id

WHERE status.id_status = 17
  AND par.status = 'Ativo'
  AND COALESCE(
        (
            SELECT MIN(status_history.create_at)
            FROM hbrd_adm_sinister_participant_status_history status_history
            WHERE status_history.create_at > status.create_at
              AND status.id_pai = status_history.id_pai
        ),
        status.leave_at
    ) IS NULL
	AND DATE(status.create_at) >= '2024-09-03'

GROUP BY tipe.id_participant;
            ")
            );
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    public static function getAccidentInvolvedForAuvoExpertiseInMotoclub(): Collection
    {
        try {
            return collect(
                DB::connection('ileva_motoclub')
                    ->select("
SELECT DISTINCT
CONCAT('mc_', par.id) external_id,
CONCAT('mc_', par.id) note,
CONCAT('mc_', par.placa) name,
'motoclub' customer_group,
CASE
WHEN par.associado = 1 THEN CONCAT('Associado: ', IFNULL(par.nome, ''), ' \\n', 'CPF: ', IFNULL(par.cpf_cnpj, ''), ' \\n', 'Placa: ', IFNULL(par.placa, ''), ' \\n', 'Montadora: ', IFNULL(par.montadora, ''), ' \\n', 'Modelo: ', IFNULL(par.modelo_veiculo, ''), ' \\n', 'Chassi: ', IFNULL(par.chassi, ''), ' \\n', 'Cor: ', IFNULL(par.cor, ''))
ELSE (
        SELECT CONCAT('Associado: ', IFNULL(par2.nome, ''), ' \\n', 'CPF: ', IFNULL(par2.cpf_cnpj, ''), ' \\n', 'Placa: ', IFNULL(par2.placa, ''), ' \\n', 'Montadora: ', IFNULL(par2.montadora, ''), ' \\n', 'Modelo: ', IFNULL(par2.modelo_veiculo, ''), ' \\n', 'Chassi: ', IFNULL(par2.chassi, ''), ' \\n', 'Cor: ', IFNULL(par2.cor, ''))
        FROM hbrd_adm_sinister_participant par2
        WHERE par2.associado = 1
        AND par2.id_sinister = par.id_sinister
        )
END `description`,
CONCAT(IFNULL(hmuc.cidade, ''), ' - ', IFNULL(hmus.estado, '')) address,
DATE_FORMAT(status.create_at, '%Y-%m-%dT%H:%i:%s') task_date
FROM hbrd_adm_sinister_participant_status_history status
LEFT JOIN hbrd_adm_sinister_participant_type_history tipe ON status.id_pai = tipe.id
LEFT JOIN hbrd_adm_sinister_status s ON status.id_status = s.id
LEFT JOIN hbrd_adm_sinister_participant par ON par.id = tipe.id_participant
LEFT JOIN hbrd_adm_sinister_history sh ON sh.id_sinister = par.id_sinister
LEFT JOIN hbrd_adm_sinister_order haso ON haso.id_participant = par.id
LEFT JOIN hbrd_adm_sinister has ON has.id = par.id_sinister
LEFT JOIN hbrd_main_util_city hmuc ON hmuc.id = par.id_cidade
LEFT JOIN hbrd_main_util_state hmus ON hmuc.id_estado = hmus.id

WHERE status.id_status = 16
  AND par.status = 'Ativo'
  AND COALESCE(
        (
            SELECT MIN(status_history.create_at)
            FROM hbrd_adm_sinister_participant_status_history status_history
            WHERE status_history.create_at > status.create_at
              AND status.id_pai = status_history.id_pai
        ),
        status.leave_at
    ) IS NULL
   AND DATE(status.create_at) >= '2024-09-03'

GROUP BY tipe.id_participant;
    ")
            );
        } catch (\Exception $e) {
            return collect([]);
        }
    }
}
