/**
 * TrendHunter Brasil - Profit Calculator Logic
 * Handles interactive calculations in real-time.
 */

$(document).ready(function() {
    // Toggle Calculator Panel visibility
    $('#floating-calc-trigger').on('click', function(e) {
        e.stopPropagation();
        $('#calculator-panel').fadeToggle(200);
    });

    // Close calculator when close button clicked
    $('#close-calculator').on('click', function() {
        $('#calculator-panel').fadeOut(200);
    });

    // Prevent closing when clicking inside the panel
    $('#calculator-panel').on('click', function(e) {
        e.stopPropagation();
    });

    // Close panel on clicking outside
    $(document).on('click', function() {
        $('#calculator-panel').fadeOut(200);
    });

    // Trigger calculation on input change
    $('.calc-input').on('input', function() {
        calculateProfit();
    });

    function calculateProfit() {
        const cost = parseFloat($('#calc-cost').val()) || 0;
        const price = parseFloat($('#calc-price').val()) || 0;
        const taxRate = parseFloat($('#calc-tax').val()) || 0;
        const feeRate = parseFloat($('#calc-fee').val()) || 0;
        const shipping = parseFloat($('#calc-shipping').val()) || 0;
        const fixedCosts = parseFloat($('#calc-fixed').val()) || 0;

        if (price <= 0 || cost <= 0) {
            // Reset results
            $('#res-profit').text('R$ 0,00');
            $('#res-margin').text('0,0%');
            $('#res-roi').text('0,0%');
            $('#res-markup').text('0,0%');
            $('#res-breakeven').text('0 un');
            return;
        }

        // Deductions calculation
        const taxDeduction = price * (taxRate / 100);
        const feeDeduction = price * (feeRate / 100);
        const totalDeductions = taxDeduction + feeDeduction + shipping;

        // Profit & Margins
        const netProfit = price - cost - totalDeductions;
        const margin = (netProfit / price) * 100;
        const markup = ((price - cost) / cost) * 100;
        const roi = (netProfit / cost) * 100;

        // Break Even
        const variableCosts = cost + totalDeductions;
        const contributionMargin = price - variableCosts;
        let breakEvenUnits = 0;
        if (contributionMargin > 0) {
            breakEvenUnits = Math.ceil(fixedCosts / contributionMargin);
        }

        // Update UI
        $('#res-profit').text('R$ ' + netProfit.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        
        // Color profit value based on gain or loss
        if (netProfit >= 0) {
            $('#res-profit').css('color', '#06e1cc');
        } else {
            $('#res-profit').css('color', '#fc5c65');
        }

        $('#res-margin').text(margin.toFixed(1) + '%');
        $('#res-roi').text(roi.toFixed(1) + '%');
        $('#res-markup').text(markup.toFixed(1) + '%');
        
        if (contributionMargin <= 0 && fixedCosts > 0) {
            $('#res-breakeven').text('Infinito (Margem Negativa)');
        } else {
            $('#res-breakeven').text(breakEvenUnits + ' un');
        }
    }
});

/**
 * Global helper to fill product values directly into the calculator from search result tables
 */
function openCalculatorWithProduct(title, price, estimatedCost = null) {
    $('#calculator-panel').fadeIn(200);
    $('#calc-price').val(price);
    
    // Auto-estimate cost if not provided (e.g. 40% of selling price as default placeholder)
    if (estimatedCost === null) {
        estimatedCost = (price * 0.45).toFixed(2);
    }
    
    $('#calc-cost').val(estimatedCost);
    
    // Trigger calculation
    $('#calc-price').trigger('input');
}
