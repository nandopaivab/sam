<?php
/**
 * TrendHunter Brasil - API Configuration Settings
 */

declare(strict_types=1);

spl_autoload_register(function ($class) {
    $prefix = 'TrendHunter\\';
    $baseDir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use TrendHunter\Auth;
use TrendHunter\Database;

Auth::requireLogin();
$user = Auth::getCurrentUser();
$userId = $user['id'] ?? 0;
$db = Database::getConnection();

// Fetch current user settings from DB
$stmt = $db->prepare("SELECT openai_api_key, gemini_api_key, ai_provider FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$userSettings = $stmt->fetch() ?: [];

$openaiKey = $userSettings['openai_api_key'] ?? '';
$geminiKey = $userSettings['gemini_api_key'] ?? '';
$aiProvider = $userSettings['ai_provider'] ?? 'local';

require __DIR__ . '/templates/header.php';
?>

<div class="container-fluid py-4 px-3 px-md-4">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom border-light-subtle gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1">
                    <i class="fa-solid fa-gears me-1"></i> CONFIGURAÇÕES
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="fa-solid fa-robot me-1"></i> Inteligência Artificial Ativa
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-white">Configuração de APIs de IA</h1>
            <p class="text-muted small mb-0">Insira suas próprias chaves de API do Google Gemini ou OpenAI para turbinar o scanner de produtos e gerar relatórios executivos 100% dinâmicos e inteligentes.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-12 col-lg-8">
            <div class="card-premium p-4">
                <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-key text-warning me-2"></i> Chaves de API e Provedor</h5>
                
                <form id="apiSettingsForm">
                    <div class="mb-4">
                        <label class="form-label text-white fw-bold mb-2">Selecione o Motor de IA</label>
                        <select class="form-select bg-dark text-white border-light-subtle p-3" id="ai_provider" name="ai_provider" style="border-radius: 8px;">
                            <option value="local" <?php echo $aiProvider === 'local' ? 'selected' : ''; ?>>Local (Simulador Padrão - Sem Chaves)</option>
                            <option value="gemini" <?php echo $aiProvider === 'gemini' ? 'selected' : ''; ?>>Google Gemini AI (Altamente Recomendado & Mais Barato/Grátis)</option>
                            <option value="openai" <?php echo $aiProvider === 'openai' ? 'selected' : ''; ?>>OpenAI GPT-4 (Excelente Desempenho)</option>
                        </select>
                        <div class="form-text text-muted mt-2">Escolha qual modelo gerará os relatórios e enriquecerá as buscas em tempo real.</div>
                    </div>

                    <!-- Gemini Section -->
                    <div class="mb-4 p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.01);">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-primary text-white me-2">Google</span>
                            <h6 class="mb-0 text-white fw-bold">Chave de API do Google Gemini</h6>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-light-subtle text-muted"><i class="fa-solid fa-code"></i></span>
                            <input type="password" class="form-control bg-dark text-white border-light-subtle p-3" id="gemini_api_key" name="gemini_api_key" value="<?php echo htmlspecialchars($geminiKey); ?>" placeholder="AIzaSy...">
                            <button class="btn btn-outline-secondary border-light-subtle" type="button" onclick="togglePasswordVisibility('gemini_api_key')">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted mt-2">
                            Obtenha sua chave gratuita/low-cost em <a href="https://aistudio.google.com/" target="_blank" class="text-accent-turquoise">Google AI Studio <i class="fa-solid fa-arrow-up-right-from-square small ms-1"></i></a>.
                        </div>
                    </div>

                    <!-- OpenAI Section -->
                    <div class="mb-4 p-3 rounded border border-light-subtle" style="background: rgba(255,255,255,0.01);">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-success text-white me-2">OpenAI</span>
                            <h6 class="mb-0 text-white fw-bold">Chave de API da OpenAI (GPT-4)</h6>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-light-subtle text-muted"><i class="fa-solid fa-code"></i></span>
                            <input type="password" class="form-control bg-dark text-white border-light-subtle p-3" id="openai_api_key" name="openai_api_key" value="<?php echo htmlspecialchars($openaiKey); ?>" placeholder="sk-proj-...">
                            <button class="btn btn-outline-secondary border-light-subtle" type="button" onclick="togglePasswordVisibility('openai_api_key')">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted mt-2">
                            Obtenha sua chave em <a href="https://platform.openai.com/api-keys" target="_blank" class="text-accent-turquoise">OpenAI Developer Platform <i class="fa-solid fa-arrow-up-right-from-square small ms-1"></i></a>.
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-5 py-3 fw-bold shadow-lg">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info / Sidebar Column -->
        <div class="col-12 col-lg-4">
            <div class="card-premium p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-circle-info text-info me-2"></i> Como Funciona o Enriquecimento IA?</h5>
                    
                    <div class="d-flex flex-column gap-3 small text-muted" style="font-size: 13px;">
                        <div>
                            <strong class="text-white d-block mb-1">1. Buscas de Concorrentes Dinâmicas:</strong>
                            Se habilitado o Gemini/OpenAI, as buscas simuladas no Scanner não usarão apenas o template padrão. A IA irá inventar concorrentes realistas, lojas reais do Brasil, preços e estatísticas com base no conhecimento de mercado dela!
                        </div>
                        <div>
                            <strong class="text-white d-block mb-1">2. Relatórios Executivos de IA:</strong>
                            No Scanner Geral e nos nichos, você poderá acionar o botão **"Gerar Relatório de IA"**. O sistema enviará os resultados em lote para a sua conta do Gemini/GPT e criará uma análise de demanda com oportunidades de lucro na hora!
                        </div>
                        <div>
                            <strong class="text-white d-block mb-1">3. Controle de Custos:</strong>
                            Toda requisição roda diretamente da sua conta, usando o consumo de tokens original do seu plano da OpenAI ou Gemini, sem intermediários.
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-light-subtle text-center">
                    <span class="text-muted small">Conexão Criptografada SSL</span>
                    <div class="small text-success fw-bold mt-1"><i class="fa-solid fa-shield-halved me-1"></i> Suas Chaves estão Seguras</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

$(document).ready(function() {
    $('#apiSettingsForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'save_api_settings');

        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Configurações de API salvas com sucesso!');
                location.reload();
            } else {
                alert('Erro ao salvar configurações: ' + (data.error || 'Erro desconhecido.'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro ao conectar-se com o servidor.');
        });
    });
});
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
