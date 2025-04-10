<?php

class ItemDescriptionHooks {
	/**
	 * Add libraries to resource loader
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		// Add as ResourceLoader Module
		$out->addModules( 'ext.ItemDescription' );
		
		// Carregar o debug se requisitado via URL
		$request = $out->getRequest();
		if ( $request->getVal( 'itemdescription-debug' ) === '1' ) {
			$out->addModules( 'ext.ItemDescription.Debug' );
		}
	}

	/**
	 * Register parser hooks
	 *
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		// Register parser functions apenas para item tooltips
		$parser->setFunctionHook( 'item-tooltip', [ __CLASS__, 'itemTooltip' ] );
		$parser->setFunctionHook( 'tip-item', [ __CLASS__, 'itemTooltip' ] );
	}
	
	/**
	 * Parser function handler for {{#tip-item: inline-text | item-json-data }}
	 *
	 * @param Parser $parser
	 * @param string $value
	 * @return array
	 */
	public static function itemTooltip( Parser $parser, string $value ) {
		$args = array_slice( func_get_args(), 2 );
		$itemData = $args[0];

		if ( !$itemData ) {
			return [];
		}

		// Limpar e sanitizar os dados JSON, preservando aspas duplas
		$itemData = Sanitizer::removeSomeTags( $itemData );
		$itemData = trim( $itemData );
		// Encode as HTML entities to preserve the JSON structure
		$itemDataAttr = htmlspecialchars( $itemData, ENT_QUOTES, 'UTF-8' );

		// Adicionar verificação para debug
		$loggedItemData = json_encode($itemData);
		wfDebugLog('itemdescription', "Item Tooltip Data: $loggedItemData");

		$html = '<span class="ethelro-item-tooltip"';
		$html .= ' data-item-tooltip="' . $itemDataAttr . '"';
		$html .= ' style="border-bottom: 1px dotted #007bff; cursor: pointer;"';
		$html .= '>' . htmlspecialchars( $value ) . '</span>';

		return [
			$html,
			'noparse' => true,
			'isHTML' => true,
			'markerType' => 'nowiki'
		];
	}
} 