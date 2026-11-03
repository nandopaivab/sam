<?php
declare(strict_types=1);

namespace TrendHunter\Helpers;

class ExportHelper {
    /**
     * Export products array to CSV format
     */
    public static function toCsv(array $products, string $filename = 'trendhunter_produtos.csv'): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Add UTF-8 BOM for Excel compatibility in Portuguese
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, [
            'ID Externo', 'Marketplace', 'Título', 'Preço (R$)', 
            'Preço Original (R$)', 'Vendas Est. (Mês)', 'Avaliações', 
            'Nota', 'Loja', 'Frete', 'Categoria', 'Trend Score', 'Concorrência'
        ], ';');
        
        foreach ($products as $p) {
            fputcsv($output, [
                $p['external_id'] ?? '',
                $p['marketplace'] ?? '',
                $p['title'] ?? '',
                number_format((float)($p['price'] ?? 0), 2, ',', ''),
                $p['original_price'] ? number_format((float)$p['original_price'], 2, ',', '') : '',
                $p['sales_count_est'] ?? 0,
                $p['reviews_count'] ?? 0,
                number_format((float)($p['rating'] ?? 0), 2, ',', ''),
                $p['store_name'] ?? '',
                $p['shipping_type'] ?? '',
                $p['category'] ?? '',
                $p['trend_score'] ?? 0,
                $p['competition_level'] ?? 'medium'
            ], ';');
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export products to Excel format (using XML SS format or semi-colon separated UTF-8 CSV labeled as XLS)
     */
    public static function toExcel(array $products, string $filename = 'trendhunter_produtos.xls'): void {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Semicolon-delimited file with BOM works perfectly when opened in Excel
        echo "\xEF\xBB\xBF";
        
        echo "ID Externo\tMarketplace\tTítulo\tPreço (R$)\tPreço Original (R$)\tVendas Est. (Mês)\tAvaliações\tNota\tLoja\tFrete\tCategoria\tTrend Score\tConcorrência\n";
        
        foreach ($products as $p) {
            $title = str_replace(["\r", "\n", "\t"], ' ', $p['title'] ?? '');
            $store = str_replace(["\r", "\n", "\t"], ' ', $p['store_name'] ?? '');
            
            echo sprintf(
                "%s\t%s\t%s\t%s\t%s\t%d\t%d\t%s\t%s\t%s\t%s\t%d\t%s\n",
                $p['external_id'] ?? '',
                $p['marketplace'] ?? '',
                $title,
                number_format((float)($p['price'] ?? 0), 2, ',', ''),
                $p['original_price'] ? number_format((float)$p['original_price'], 2, ',', '') : '',
                $p['sales_count_est'] ?? 0,
                $p['reviews_count'] ?? 0,
                number_format((float)($p['rating'] ?? 0), 2, ',', ''),
                $store,
                $p['shipping_type'] ?? '',
                $p['category'] ?? '',
                $p['trend_score'] ?? 0,
                $p['competition_level'] ?? 'medium'
            );
        }
        exit;
    }

    /**
     * Export report to PDF by outputting a print-friendly HTML design
     * This avoids installing complex PDF extensions and uses browser PDF rendering engine
     */
    public static function toPdfHtml(array $products, string $reportTitle = 'Relatório de Tendências de Produtos'): void {
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title><?php echo htmlspecialchars($reportTitle); ?></title>
            <!-- FontAwesome Icons for print UI -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 20px; line-height: 1.4; }
                .header { text-align: center; border-bottom: 2px solid #5d59eb; padding-bottom: 15px; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #1a194d; font-size: 24px; }
                .header p { margin: 5px 0 0 0; color: #666; font-size: 14px; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 11px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5fc; color: #1a194d; font-weight: bold; }
                tr:nth-child(even) { background-color: #fcfcfc; }
                .score-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; color: #fff; text-align: center; }
                .score-high { background-color: #198754; }
                .score-mid { background-color: #ffc107; color: #000; }
                .score-low { background-color: #dc3545; }
                @media print {
                    .no-print { display: none; }
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            <div class="no-print" style="background:#5d59eb;color:#fff;padding:10px;margin-bottom:20px;border-radius:4px;display:flex;justify-content:between;align-items:center;">
                <span>📄 Visualize o relatório abaixo. Pressione <strong>Ctrl + P</strong> (ou Cmd + P no Mac) para Salvar como PDF.</span>
                <button onclick="window.print()" style="margin-left:auto;background:#fff;color:#5d59eb;border:none;padding:5px 15px;border-radius:3px;cursor:pointer;font-weight:bold;">Imprimir / PDF</button>
            </div>
            
            <div class="header">
                <h1>TrendHunter Brasil - Relatório de Inteligência</h1>
                <p><?php echo htmlspecialchars($reportTitle); ?> | Gerado em: <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">Imagem</th>
                        <th>Marketplace</th>
                        <th>Título / Anúncio</th>
                        <th>Preço (R$)</th>
                        <th>Vendas Est.</th>
                        <th>Avaliações</th>
                        <th>Nota</th>
                        <th>Loja</th>
                        <th>Frete</th>
                        <th>Trend Score</th>
                        <th>Concorrência</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): 
                        $score = $p['trend_score'] ?? 0;
                        $badgeClass = 'score-low';
                        if ($score >= 80) $badgeClass = 'score-high';
                        elseif ($score >= 40) $badgeClass = 'score-mid';
                        ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($p['image_url'] ?? ''); ?>" alt="Foto" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; display: block;"></td>
                            <td><strong><?php echo htmlspecialchars(ucfirst($p['marketplace'] ?? '')); ?></strong></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($p['url'] ?? '#'); ?>" target="_blank" style="color: #5d59eb; text-decoration: none; font-weight: 600;">
                                    <?php echo htmlspecialchars($p['title'] ?? ''); ?>
                                    <i class="fa-solid fa-up-right-from-square" style="font-size: 8px; margin-left: 2px;"></i>
                                </a>
                            </td>
                            <td>R$ <?php echo number_format((float)($p['price'] ?? 0), 2, ',', '.'); ?></td>
                            <td><?php echo number_format((int)($p['sales_count_est'] ?? 0), 0, ',', '.'); ?>/mês</td>
                            <td><?php echo number_format((int)($p['reviews_count'] ?? 0), 0, ',', '.'); ?></td>
                            <td><?php echo number_format((float)($p['rating'] ?? 0), 1, ',', '.'); ?> ★</td>
                            <td><?php echo htmlspecialchars($p['store_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($p['shipping_type'] ?? ''); ?></td>
                            <td><span class="score-badge <?php echo $badgeClass; ?>"><?php echo $score; ?></span></td>
                            <td><?php echo htmlspecialchars(ucfirst($p['competition_level'] ?? 'medium')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <script>
                // Auto trigger print dialogue if explicitly desired
                if (window.location.search.indexOf('autoprint=1') !== -1) {
                    window.print();
                }
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}
