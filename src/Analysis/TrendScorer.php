<?php
declare(strict_types=1);

namespace TrendHunter\Analysis;

class TrendScorer {
    /**
     * Calculate Trend Score (0 - 100) based on weighted parameters
     * 
     * Formula:
     * Score = 0.35 * Demand + 0.25 * Growth + 0.20 * (100 - Competition) + 0.15 * Margin + 0.05 * (100 - Seasonality)
     */
    public function calculate(
        int $demand,          // 0 to 100 (based on sales volume)
        int $growth,          // 0 to 100 (based on search volume growth)
        int $competitionScore,// 0 to 100 (100 is extremely crowded, 0 is blue ocean)
        int $profitMargin,    // 0 to 100 (average profit margin percentage)
        int $seasonality      // 0 to 100 (100 is high seasonal spike like Christmas, 0 is stable year-round)
    ): int {
        // Constrain values to 0-100 range
        $demand = max(0, min(100, $demand));
        $growth = max(0, min(100, $growth));
        $competitionScore = max(0, min(100, $competitionScore));
        $profitMargin = max(0, min(100, $profitMargin));
        $seasonality = max(0, min(100, $seasonality));

        $competitionFactor = 100 - $competitionScore;
        $seasonalityFactor = 100 - $seasonality;

        $score = (0.35 * $demand) +
                 (0.25 * $growth) +
                 (0.20 * $competitionFactor) +
                 (0.15 * $profitMargin) +
                 (0.05 * $seasonalityFactor);

        return (int)round(max(0, min(100, $score)));
    }

    /**
     * Get qualitative label based on the calculated Trend Score
     */
    public static function getLabel(int $score): string {
        return match (true) {
            $score >= 80 => 'Excelente Potencial (Estrela)',
            $score >= 60 => 'Promissor (Alto Potencial)',
            $score >= 40 => 'Estável (Concorrência Média)',
            $score >= 25 => 'Saturado / Alta Concorrência',
            default => 'Baixo Retorno / Risco Alto',
        };
    }

    /**
     * Get color class (bootstrap compatible) for UI badges based on score
     */
    public static function getColorClass(int $score): string {
        return match (true) {
            $score >= 80 => 'success', // green
            $score >= 60 => 'info',    // teal/light blue
            $score >= 40 => 'warning', // yellow/orange
            default => 'danger',       // red
        };
    }
}
