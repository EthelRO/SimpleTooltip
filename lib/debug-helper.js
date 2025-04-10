/**
 * Debug Helper para ItemDescription
 * 
 * Este arquivo ajuda a diagnosticar problemas com os tooltips
 */
(function() {
    'use strict';

    // Verificar se estamos em uma página MediaWiki
    if (typeof mw === 'undefined') {
        console.error('MediaWiki não detectado!');
        return;
    }

    // Aguardar o carregamento da página
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[EthelRO Debug] Iniciando verificação de tooltips...');
        
        // Verificar os tooltips de item
        const itemTooltips = document.querySelectorAll('.ethelro-item-tooltip');
        console.log(`[EthelRO Debug] Encontrados ${itemTooltips.length} tooltips de item na página:`, itemTooltips);
        
        itemTooltips.forEach((el, index) => {
            const rawData = el.getAttribute('data-item-tooltip');
            console.log(`[EthelRO Debug] Tooltip #${index+1} - Texto exibido: "${el.textContent}"`);
            console.log(`[EthelRO Debug] Tooltip #${index+1} - Dados brutos:`, rawData);
            
            try {
                // Tentar parser os dados como JSON
                let itemData;
                
                // Método 1: Parser direto
                try {
                    itemData = JSON.parse(rawData);
                    console.log(`[EthelRO Debug] Tooltip #${index+1} - Parser direto OK:`, itemData);
                } catch (e) {
                    console.warn(`[EthelRO Debug] Tooltip #${index+1} - Falha no parser direto:`, e.message);
                    
                    // Método 2: Substituir single quotes por double quotes
                    try {
                        const fixedQuotes = rawData.replace(/'/g, '"');
                        itemData = JSON.parse(fixedQuotes);
                        console.log(`[EthelRO Debug] Tooltip #${index+1} - Parser com substituição de aspas OK:`, itemData);
                    } catch (e2) {
                        console.warn(`[EthelRO Debug] Tooltip #${index+1} - Falha no parser com substituição de aspas:`, e2.message);
                        
                        // Método 3: Substituir entidades HTML
                        try {
                            const decodedHtml = rawData
                                .replace(/&quot;/g, '"')
                                .replace(/&#039;/g, "'")
                                .replace(/&amp;/g, '&');
                            itemData = JSON.parse(decodedHtml);
                            console.log(`[EthelRO Debug] Tooltip #${index+1} - Parser com decode de HTML OK:`, itemData);
                        } catch (e3) {
                            console.error(`[EthelRO Debug] Tooltip #${index+1} - Falha em todos os métodos de parsing.`);
                        }
                    }
                }
            } catch (e) {
                console.error(`[EthelRO Debug] Tooltip #${index+1} - Erro global:`, e);
            }
        });
        
        // Verificar se os scripts necessários foram carregados
        if (typeof mw.libs.ItemDescription === 'undefined') {
            console.error('[EthelRO Debug] Módulo ItemDescription não foi carregado!');
        } else {
            console.log('[EthelRO Debug] Módulo ItemDescription carregado corretamente.');
        }
        
        // Inserir um tooltip de teste para garantir
        console.log('[EthelRO Debug] Inserindo tooltip de teste...');
        const testContainer = document.createElement('div');
        testContainer.innerHTML = '<span class="ethelro-item-tooltip" data-item-tooltip=\'{"id":9999,"name":"Debug Item","description":"Item de teste para debug","imageUrl":"https://assets.ethelro.com/item/9999/image.png"}\'>Debug Item (Hover)</span>';
        testContainer.innerHTML += '<br>Exemplo de item com ícone: <img src="https://assets.ethelro.com/item/9999.png" class="ethelro-item-icon" style="vertical-align: middle;"> Debug Item';
        
        // Adicionar ao final da página
        document.body.appendChild(testContainer);
        
        // Re-inicializar os tooltips
        if (typeof mw.libs.ItemDescription !== 'undefined' && typeof mw.libs.ItemDescription.init === 'function') {
            setTimeout(function() {
                console.log('[EthelRO Debug] Re-inicializando tooltips...');
                mw.libs.ItemDescription.init();
            }, 1000);
        }
    });
    
    // Verificar erros de CSS
    window.addEventListener('load', function() {
        console.log('[EthelRO Debug] Verificando estilos...');
        const styles = document.styleSheets;
        let foundItemDescriptionCss = false;
        
        for (let i = 0; i < styles.length; i++) {
            try {
                const href = styles[i].href || '';
                if (href.includes('ItemDescription.css')) {
                    console.log('[EthelRO Debug] CSS do ItemDescription encontrado:', href);
                    foundItemDescriptionCss = true;
                    break;
                }
            } catch (e) {
                // Alguns browsers bloqueiam acesso a folhas de estilo de outros domínios
                console.warn('[EthelRO Debug] Não foi possível verificar uma folha de estilo:', e);
            }
        }
        
        if (!foundItemDescriptionCss) {
            console.error('[EthelRO Debug] CSS do ItemDescription não encontrado!');
        }
    });
})(); 