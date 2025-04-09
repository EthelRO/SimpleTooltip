/**
 * ItemDescription component for SimpleTooltip Extension
 * 
 * Adapted from EthelRO ItemDescription React component
 */
(function (mw, $) {
    'use strict';

    // Namespace for ItemDescription
    mw.libs.ItemDescription = mw.libs.ItemDescription || {};

    /**
     * Format text with special colored tags
     * @param {string} description - The item description text
     * @param {number} itemId - The item ID
     * @returns {string} - Formatted HTML string
     */
    function formatText(description, itemId) {
        if (!description) return "";

        const lightBlueColor = "4EBAFE";

        let formattedText = `<span style="color:#${lightBlueColor};">[EthelRO]</span><br />${description}<br /><span style="color:#${lightBlueColor}; display: inline-block; margin-bottom: 10px;">[${itemId || "ID"}]</span>`;

        formattedText = formattedText
            .replace(/\^([0-9A-Fa-f]{6})/g, (_, color) => `<span style="color:#${color};">`)
            .replaceAll("\n", "<br />");

        // Check for unbalanced tags and close them
        const openTags = (formattedText.match(/<span style="color:/g) || []).length;
        const closeTags = (formattedText.match(/<\/span>/g) || []).length;

        if (openTags > closeTags) {
            formattedText += "</span>".repeat(openTags - closeTags);
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
     * @param {number} min - Minimum displayed chars
     * @param {number} max - Maximum displayed chars
     * @returns {string} - Formatted display name
     */
    function formatItemDisplayName(name, refine, grade, min, max) {
        if (!name) return "";
        
        let displayName = name;
        
        if (refine && refine > 0) {
            displayName = `+${refine} ${displayName}`;
        }
        
        return displayName;
    }

    /**
     * Render item tooltip 
     * @param {Object} item - The item data
     * @param {string} containerSelector - The container selector
     */
    mw.libs.ItemDescription.render = function(item, containerSelector) {
        if (!item || !containerSelector) return;
        
        const container = document.querySelector(containerSelector);
        if (!container) return;
        
        // Format the item data
        const displayName = formatItemDisplayName(item.name, item.refine, item.enchantgrade, 0, 22);
        const gradeBackgroundClass = getGradeColorByValue(item.enchantgrade);
        const gradeColor = getGradeHexColor(item.enchantgrade);
        const gradeBorderColor = getGradeBorderColor(item.enchantgrade);
        
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
                            <img src="${item.imageUrl || item.id}" alt="${item.name}" />
                        </div>
                        <div class="tooltip-info">
                            <div class="tooltip-title">
                                <h1>${displayName}</h1>
                            </div>
                            <div class="tooltip-description">
                                <p class="item-description" 
                                   dangerouslySetInnerHTML="${formatText(item.description, item.id)}">
                                </p>
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
        const itemTooltips = document.querySelectorAll('[data-item-tooltip]');
        
        itemTooltips.forEach(el => {
            try {
                const itemData = JSON.parse(el.getAttribute('data-item-tooltip'));
                
                el.addEventListener('mouseenter', () => {
                    const tooltipContainer = document.createElement('div');
                    tooltipContainer.className = 'item-tooltip-container';
                    document.body.appendChild(tooltipContainer);
                    
                    // Position the tooltip
                    const rect = el.getBoundingClientRect();
                    tooltipContainer.style.position = 'absolute';
                    tooltipContainer.style.left = `${rect.right + 10}px`;
                    tooltipContainer.style.top = `${rect.top}px`;
                    tooltipContainer.style.zIndex = '9999999';
                    
                    // Render the tooltip
                    mw.libs.ItemDescription.render(itemData, '.item-tooltip-container');
                });
                
                el.addEventListener('mouseleave', () => {
                    const container = document.querySelector('.item-tooltip-container');
                    if (container) {
                        container.remove();
                    }
                });
            } catch (e) {
                console.error('Failed to parse item data', e);
            }
        });
    };

    // Initialize on DOM ready
    $(function() {
        mw.libs.ItemDescription.init();
    });

}(mediaWiki, jQuery)); 