<?php

namespace App\Jobs;

use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Dompdf\Dompdf;

class GeneratePdfAuvoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected AuvoCustomerDTO $customer,
        protected AuvoTaskDTO $taskData,
        protected string $accessToken
    ) {}

    public function handle(): void
    {
        $pdfPath = $this->generatePdf($this->customer);

        $pdfBase64 = base64_encode(file_get_contents($pdfPath));
        $this->taskData->attachments =
            [
                'name' => 'ordem_resumo_' . $this->customer->externalId . '.pdf',
                'file' => $pdfBase64,
            ];

        dispatch(new UpdateAuvoTaskJob($this->accessToken, $this->taskData));
    }

    private function generatePdf($customer): string
    {
        $dompdf = new Dompdf();
        $orderItems = json_decode($customer->order_items, true);
        $orderSummary = json_decode($customer->order_summary, true);

        $html = '
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        h3 {
            text-align: center;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            padding: 8px;
            text-align: center;
            font-size: 10px;
        }
        td {
            padding: 8px;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .highlight {
            color: red;
        }
        .total {
            font-weight: bold;
            font-size: 12px;
        }
    </style>
    <h3>Resumo do pedido: ' . $customer->id_order . '</h3>
    <h3>' . $customer->orientation . '</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Qtd.</th>
                <th>Troca</th>
                <th>Lanternagem</th>
                <th>Pintura</th>
                <th>Observação</th>
                <th>Valor</th>
                <th>Desconto</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($orderItems as $item) {
            $quantidade = !empty($item['quantidade']) ? $item['quantidade'] : '0';
            $descricao = !empty($item['descricao']) ? $item['descricao'] : '0';
            $valor = !empty($item['valor']) ? 'R$ ' . $item['valor'] : 'R$ 0';
            $desconto = !empty($item['desconto']) ? 'R$ ' . $item['desconto'] : 'R$ 0';
            $observacao = !empty($item['observacao']) ? $item['observacao'] : '0';
            $troca = isset($item['troca']) ? ($item['troca'] ? 'Sim' : 'Não') : 'Não';
            $lanternagem = isset($item['lanternagem']) ? ($item['lanternagem'] ? 'Sim' : 'Não') : 'Não';
            $pintura = isset($item['pintura']) ? ($item['pintura'] ? 'Sim' : 'Não') : 'Não';

            $html .= '
                <tr>
                    <td>' . $descricao . '</td>
                    <td>' . $quantidade . '</td>
                    <td>' . $troca . '</td>
                    <td>' . $lanternagem . '</td>
                    <td>' . $pintura . '</td>
                    <td>' . $observacao . '</td>
                    <td>' . $valor . '</td>
                    <td>' . $desconto . '</td>
                </tr>';
        }

        // Verificar valores no orderSummary
        $subtotal = !empty($orderSummary['subtotal']) ? 'R$ ' . $orderSummary['subtotal'] : 'R$ 0';
        $valorMaoObra = !empty($orderSummary['valor_maoobra']) ? 'R$ ' . $orderSummary['valor_maoobra'] : 'R$ 0';
        $valorDesconto = !empty($orderSummary['valor_desconto']) ? 'R$ -' . $orderSummary['valor_desconto'] : 'R$ 0';
        $valorDescontoItens = !empty($orderSummary['valor_desconto_itens']) ? 'R$ -' . $orderSummary['valor_desconto_itens'] : 'R$ 0';
        $valorDescontoNegociacao = !empty($orderSummary['valor_desconto_negociacao']) ? 'R$ -' . $orderSummary['valor_desconto_negociacao'] : 'R$ 0';
        $ajudaParticipativa = !empty($orderSummary['ajuda_participativa']) ? 'R$ -' . $orderSummary['ajuda_participativa'] : 'R$ 0';
        $valorTotal = !empty($orderSummary['valor_total']) ? 'R$ ' . $orderSummary['valor_total'] : 'R$ 0';

        $html .= '
        </tbody>
    </table>
    <h3>Valores a cobrar</h3>
    <table>
        <tr>
            <td>Subtotal</td>
            <td>' . $subtotal . '</td>
        </tr>
        <tr>
            <td>Valor da Mão de Obra</td>
            <td>' . $valorMaoObra . '</td>
        </tr>
        <tr>
            <td>Desconto da Oficina</td>
            <td class="highlight">' . $valorDesconto . '</td>
        </tr>
        <tr>
            <td>Desconto dos Itens</td>
            <td class="highlight">' . $valorDescontoItens . '</td>
        </tr>
        <tr>
            <td>Desconto na Negociação</td>
            <td class="highlight">' . $valorDescontoNegociacao . '</td>
        </tr>
        <tr>
            <td>Ajuda Participativa</td>
            <td class="highlight">' . $ajudaParticipativa . '</td>
        </tr>
        <tr>
            <td class="total">Valor Total</td>
            <td class="total">' . $valorTotal . '</td>
        </tr>
    </table>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $pdfPath = storage_path('app/public/order_' . $customer->id . '.pdf');
        file_put_contents($pdfPath, $output);

        return $pdfPath;
    }
}
