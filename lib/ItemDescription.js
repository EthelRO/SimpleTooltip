/**
 * ItemDescription component for SimpleTooltip Extension
 * 
 * Adapted from EthelRO ItemDescription React component
 */
(function (mw, $) {
    'use strict';

    // Namespace for ItemDescription
    mw.libs.ItemDescription = mw.libs.ItemDescription || {};

    // Função de depuração
    function debugLog(msg, obj) {
        if (window.console && window.console.log) {
            if (obj) {
                console.log('[ItemDescription]', msg, obj);
            } else {
                console.log('[ItemDescription]', msg);
            }
        }
    }

    /**
     * Format text with special colored tags
     * @param {string} description - The item description text
     * @param {number} itemId - The item ID
     * @returns {string} - Formatted HTML string
     */
    function formatText(description, itemId) {
        if (!description) return "";

        const lightBlueColor = "4EBAFE";

        // Formatar o texto inicial
        let formattedText = `<span style="color:#${lightBlueColor};">[EthelRO]</span><br />${description}`;
        
        // Adicionar ID no final
        formattedText += `<br /><span style="color:#${lightBlueColor}; display: inline-block; margin-top: 8px;">[${itemId || "ID"}]</span>`;

        // Substituir traços consecutivos com divisores
        formattedText = formattedText.replace(/(-{3,})/g, '---DIVIDER---');
        
        // Processar cores e quebras de linha
        formattedText = formattedText
            .replace(/\^([0-9A-Fa-f]{6})/g, (_, color) => `<span style="color:#${color};">`)
            .replace(/\n/g, "<br />");
            
        // Converter os marcadores de divisores em divs de separação
        formattedText = formattedText
            .replace(/---DIVIDER---/g, '</p><div class="hr-divider"></div><p class="item-description">')
            .replace(/<br \/>\s*<\/p>/g, '</p>')
            .replace(/<p class="item-description"><br \/>/g, '<p class="item-description">');
        
        // Se o texto não começar com um parágrafo, envolva-o
        if (!formattedText.startsWith('<p class="item-description">')) {
            formattedText = '<p class="item-description">' + formattedText;
        }
        
        // Se o texto não terminar com um parágrafo, feche-o
        if (!formattedText.endsWith('</p>')) {
            formattedText = formattedText + '</p>';
        }

        // Check for unbalanced tags and close them
        const openTags = (formattedText.match(/<span style="color:/g) || []).length;
        const closeTags = (formattedText.match(/<\/span>/g) || []).length;

        if (openTags > closeTags) {
            // Inserir os fechamentos de span antes do último </p>
            const lastPIndex = formattedText.lastIndexOf('</p>');
            if (lastPIndex !== -1) {
                formattedText = formattedText.slice(0, lastPIndex) + "</span>".repeat(openTags - closeTags) + formattedText.slice(lastPIndex);
            } else {
                formattedText += "</span>".repeat(openTags - closeTags);
            }
        }

        return formattedText;
    }

    /**
     * Get grade color based on value
     * @param {number} grade - The enchant grade
     * @returns {string} - CSS class for the grade
     */
    function getGradeColorByValue(grade) {
        if (!grade) return "";
        
        const gradeClasses = [
            "",
            "bg-grade-1",
            "bg-grade-2",
            "bg-grade-3",
            "bg-grade-4"
        ];
        
        return gradeClasses[Math.min(grade, 4)];
    }

    /**
     * Get grade hex color
     * @param {number} grade - The enchant grade
     * @returns {string} - Hex color code
     */
    function getGradeHexColor(grade) {
        if (!grade) return "#c3c3c3";
        
        const gradeColors = [
            "#c3c3c3",
            "#95d636",
            "#03adfc",
            "#cd5efc",
            "#ffce37"
        ];
        
        return gradeColors[Math.min(grade, 4)];
    }

    /**
     * Get grade border color
     * @param {number} grade - The enchant grade
     * @returns {string} - Hex color code for border
     */
    function getGradeBorderColor(grade) {
        if (!grade) return "#c3c3c3";
        
        const gradeBorderColors = [
            "#c3c3c3",
            "#7eb82a",
            "#0387c3",
            "#a42ed6",
            "#d6a72e"
        ];
        
        return gradeBorderColors[Math.min(grade, 4)];
    }

    /**
     * Format item display name
     * @param {string} name - The item name
     * @param {number} refine - The refine level
     * @param {number} grade - The enchant grade
     * @param {number} slots - The number of slots
     * @param {number} min - Minimum displayed chars
     * @param {number} max - Maximum displayed chars
     * @returns {string} - Formatted display name
     */
    function formatItemDisplayName(name, refine, grade, slots, min, max) {
        if (!name) return "";
        
        let displayName = name;
        
        if (refine && refine > 0) {
            displayName = `+${refine} ${displayName}`;
        }
        
        // Adicionar número de slots, se existir
        if (slots && slots > 0) {
            displayName = `${displayName} [${slots}]`;
        }
        
        return displayName;
    }

    /**
     * Render item tooltip 
     * @param {Object} item - The item data
     * @param {HTMLElement} container - The container element
     */
    mw.libs.ItemDescription.render = function(item, container) {
        if (!item || !container) {
            debugLog('Missing item or container for render', { item, container });
            return;
        }
        
        debugLog('Rendering tooltip for item', item);
        
        // Format the item data
        const displayName = formatItemDisplayName(item.name, item.refine, item.enchantgrade, item.slots, 0, 22);
        const gradeBackgroundClass = getGradeColorByValue(item.enchantgrade);
        const gradeColor = getGradeHexColor(item.enchantgrade);
        const gradeBorderColor = getGradeBorderColor(item.enchantgrade);
        
        // Placeholder image if none provided
        const imageSrc = item.imageUrl || 'https://static.wikia.nocookie.net/ragnarok_gamepedia_en/images/9/93/Unknown_Item.png';
        
        // Create HTML structure
        const tooltipHTML = `
            <div class="ethelro-tooltip">
                <div class="tooltip-container ${item.enchantgrade ? "gradient-border" : ""}" 
                     style="${item.enchantgrade ? `--border-start-color: ${gradeBorderColor};` : 'border: 1px solid #c3c3c3;'}">
                    <div class="grade-header ${gradeBackgroundClass}" 
                         style="border-color: ${item.enchantgrade ? gradeBorderColor : '#c3c3c3'};">
                    </div>
                    <div class="tooltip-content">
                        <div class="tooltip-image">
                            <img src="${imageSrc}" alt="${item.name}" style="width: 77px; height: 102px;" />
                        </div>
                        <div class="tooltip-info">
                            <div class="tooltip-title">
                                <div class="title-divider-top"></div>
                                <h1>${displayName}</h1>
                                <div class="title-divider-bottom"></div>
                            </div>
                            <div class="tooltip-description">
                                ${formatText(item.description, item.id)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = tooltipHTML;
    };

    /**
     * Initialize the item tooltip for elements with data attribute
     */
    mw.libs.ItemDescription.init = function() {
        debugLog('Initializing ItemDescription tooltips');
        
        const itemTooltips = document.querySelectorAll('.ethelro-item-tooltip');
        debugLog(`Found ${itemTooltips.length} item tooltips`);
        
        itemTooltips.forEach((el, index) => {
            debugLog(`Processing tooltip ${index+1}`, el);
            
            try {
                let itemDataStr = el.getAttribute('data-item-tooltip');
                debugLog('Raw item data string', itemDataStr);
                
                // Replace single quotes with double quotes for valid JSON
                itemDataStr = itemDataStr.replace(/'/g, '"');
                
                // Parse the JSON data
                let itemData;
                try {
                    itemData = JSON.parse(itemDataStr);
                } catch (jsonError) {
                    console.error('JSON parse error:', jsonError, 'for string:', itemDataStr);
                    // Fallback: try to manually parse by replacing HTML entities
                    itemDataStr = itemDataStr
                        .replace(/&quot;/g, '"')
                        .replace(/&#039;/g, "'")
                        .replace(/&amp;/g, '&');
                    try {
                        itemData = JSON.parse(itemDataStr);
                    } catch (e) {
                        throw new Error('Failed to parse JSON data: ' + e.message);
                    }
                }
                
                debugLog('Parsed item data', itemData);
                
                el.addEventListener('mouseenter', () => {
                    debugLog('Mouse entered element', el);
                    
                    // Remove any existing tooltips
                    const existingContainer = document.querySelector('.item-tooltip-container');
                    if (existingContainer) {
                        existingContainer.remove();
                    }
                    
                    // Create tooltip container
                    const tooltipContainer = document.createElement('div');
                    tooltipContainer.className = 'item-tooltip-container';
                    document.body.appendChild(tooltipContainer);
                    
                    // Position the tooltip
                    const rect = el.getBoundingClientRect();
                    tooltipContainer.style.position = 'absolute';
                    
                    // Verificar se há espaço suficiente à direita
                    const rightSpace = window.innerWidth - rect.right;
                    if (rightSpace < 320) { // Se não houver espaço suficiente à direita
                        tooltipContainer.style.right = '10px';
                        tooltipContainer.style.left = 'auto';
                    } else {
                        tooltipContainer.style.left = `${rect.right + 10}px`;
                        tooltipContainer.style.right = 'auto';
                    }
                    
                    tooltipContainer.style.top = `${rect.top}px`;
                    tooltipContainer.style.zIndex = '9999999';
                    
                    // Render the tooltip
                    mw.libs.ItemDescription.render(itemData, tooltipContainer);
                });
                
                el.addEventListener('mouseleave', () => {
                    debugLog('Mouse left element', el);
                    
                    // Delay removing the tooltip slightly to allow hovering the tooltip itself
                    setTimeout(() => {
                        const container = document.querySelector('.item-tooltip-container');
                        if (container && !container.matches(':hover')) {
                            container.remove();
                        }
                    }, 100);
                });
            } catch (e) {
                console.error('Failed to setup item tooltip:', e);
            }
        });
    };

    // Initialize on DOM ready
    $(function() {
        debugLog('DOM ready, initializing ItemDescription');
        mw.libs.ItemDescription.init();
        
        // Re-initialize tooltips when any content changes (for dynamically added tooltips)
        mw.hook('wikipage.content').add(function() {
            debugLog('Content updated, re-initializing tooltips');
            mw.libs.ItemDescription.init();
        });
    });

}(mediaWiki, jQuery)); 