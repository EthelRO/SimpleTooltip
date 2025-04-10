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
		$out->addModules( 'ext.SimpleTooltip' );
		
		// Carregar o debug se requisitado via URL
		$request = $out->getRequest();
		if ( $request->getVal( 'itemdescription-debug' ) === '1' ) {
			$out->addModules( 'ext.SimpleTooltip.Debug' );
		}
	}

	/**
	 * Register parser hooks
	 *
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		// Registrar apenas o hook item por enquanto
		$parser->setFunctionHook( 'item', [ __CLASS__, 'itemDisplay' ] );
	}
	
	/**
	 * Parser function handler for {{#tip-item: inline-text | item-json-data }}
	 * Temporariamente desativado
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
		$html .= ' style="cursor: pointer;"';
		$html .= '>' . htmlspecialchars( $value ) . '</span>';

		return [
			$html,
			'noparse' => true,
			'isHTML' => true,
			'markerType' => 'nowiki'
		];
	}
	
	/**
	 * Parser function handler for {{#item: id | param1=value1 | ... }}
	 * Displays an item icon with name and description by default.
	 * 
	 * Usage examples:
	 * {{#item: 502}} - Shows icon, name and description (tooltip)
	 * {{#item: 502 | noname}} or {{#item: 502 | nn}} - Hides the item name
	 * {{#item: 502 | nodescription}} or {{#item: 502 | nd}} - Removes tooltip
	 * {{#item: 502 | noslots}} or {{#item: 502 | ns}} - Hides slots information
	 * {{#item: 502 | norefine}} or {{#item: 502 | nr}} - Hides refine level
	 * {{#item: 502 | width=32}} - Sets custom icon size
	 * {{#item: 502 | noname | nodescription}} - Shows only the icon
	 *
	 * @param Parser $parser
	 * @param string $itemId The item ID to display
	 * @return array HTML and parser options
	 */
	public static function itemDisplay( Parser $parser, string $itemId ) {
		global $wgRequest;
		
		// Verificação básica de ID
		$itemId = trim($itemId);
		if (!is_numeric($itemId)) {
			return ['<span class="error">ID do item inválido: ' . htmlspecialchars($itemId) . '</span>'];
		}
		
		// Processar parâmetros adicionais
		$args = array_slice(func_get_args(), 2);
		$params = [];
		
		// Valores padrão dos parâmetros - agora true por padrão
		$params['showname'] = true;
		$params['showdescription'] = true;
		$params['showslots'] = true; // Mostrar slots por padrão
		$params['showrefine'] = true; // Mostrar nível de refino por padrão
		$params['custom_size'] = false; // Não usar tamanho personalizado por padrão
		$params['icon_width'] = 24; // Largura padrão para o ícone se especificado
		$params['cache'] = true; // Cache habilitado por padrão
		
		// Processar argumentos nomeados e não-nomeados
		foreach ($args as $arg) {
			$arg = trim($arg);
			
			// Verificar se é um parâmetro nomeado (contém '=')
			if (strpos($arg, '=') !== false) {
				$pair = explode('=', $arg, 2);
				if (count($pair) == 2) {
					$key = strtolower(trim($pair[0]));
					$value = trim($pair[1]);
					
					if ($key == 'width' || $key == 'icon_width' || $key == 'w') {
						$params['icon_width'] = (int)$value;
						$params['custom_size'] = true; // Marcar que um tamanho personalizado foi especificado
					} elseif ($key == 'cache') {
						$params['cache'] = self::parseBoolean($value);
					}
				}
			} 
			// Processar argumentos não-nomeados (flags)
			else {
				$flag = strtolower($arg);
				
				// Flags para desativar nome e descrição
				if ($flag == 'noname' || $flag == 'nn') {
					$params['showname'] = false;
				} elseif ($flag == 'nodescription' || $flag == 'nodesc' || $flag == 'nd') {
					$params['showdescription'] = false;
				} elseif ($flag == 'noslots' || $flag == 'ns') {
					$params['showslots'] = false;
				} elseif ($flag == 'norefine' || $flag == 'nr') {
					$params['showrefine'] = false;
				}
				// Se quiser adicionar outras flags no futuro
				elseif ($flag == 'nocache' || $flag == 'nc') {
					$params['cache'] = false;
				}
			}
		}
		
		// URLs do item
		$iconUrl = 'https://assets.ethelro.com/item/' . $itemId . '.png'; // Ícone para exibir ao lado do nome
		$imageUrl = 'https://assets.ethelro.com/item/' . $itemId . '/image.png'; // Imagem completa para o tooltip
		
		// Inicializar HTML
		$html = '';
		
		// Sempre buscar dados do item já que agora são exibidos por padrão
		$itemData = self::fetchItemData($itemId, $params['cache'], $parser);
		
		// Verificar se o item existe
		if (!$itemData) {
			return ['<span class="error">Item não encontrado: ' . htmlspecialchars($itemId) . '</span>'];
		}
		
		// Se showdescription estiver habilitado, adicionamos o tooltip
		if ($params['showdescription']) {
			// Preparar dados para o tooltip
			$tooltipData = [
				'id' => $itemData['id'],
				'name' => $itemData['name'],
				'description' => $itemData['description'],
				'imageUrl' => $imageUrl, // Usar a imagem completa no tooltip
				'slots' => $itemData['slots'] ?? 0
			];
			
			// Adicionar nível de refino se disponível
			if (isset($itemData['refine']) && $itemData['refine'] > 0) {
				$tooltipData['refine'] = $itemData['refine'];
			}
			
			// Adicionar grau de encantamento se disponível
			if (isset($itemData['enchantgrade']) && $itemData['enchantgrade'] > 0) {
				$tooltipData['enchantgrade'] = $itemData['enchantgrade'];
			}
			
			// Codificar para JSON
			$jsonData = json_encode($tooltipData);
			$jsonAttr = htmlspecialchars($jsonData, ENT_QUOTES, 'UTF-8');
			
			// Iniciar o elemento com tooltip
			$html .= '<span class="ethelro-item-tooltip" data-item-tooltip="' . $jsonAttr . '">';
		}
		
		// Adicionar imagem do item
		$html .= '<img src="' . htmlspecialchars($iconUrl) . '" alt="Item #' . htmlspecialchars($itemId) . '" ';
		$html .= 'class="ethelro-item-icon"';
		
		// Adicionar width/height apenas se tamanho personalizado foi especificado
		if ($params['custom_size']) {
			$html .= ' width="' . (int)$params['icon_width'] . '" height="' . (int)$params['icon_width'] . '"';
		}
		
		$html .= ' style="vertical-align: middle;"';
		$html .= '>';
		
		// Adicionar nome se showname estiver habilitado
		if ($params['showname']) {
			// Adicionar prefixo de refino se disponível e habilitado
			$refinePrefix = '';
			if ($params['showrefine'] && isset($itemData['refine']) && $itemData['refine'] > 0) {
				$refinePrefix = '+' . $itemData['refine'] . ' ';
			}
			
			$html .= ' <span class="ethelro-item-name" style="text-decoration: none;">' . $refinePrefix . htmlspecialchars($itemData['name']);
			
			// Adicionar slots se showslots estiver habilitado e slots for diferente de 0
			$slots = isset($itemData['slots']) ? (int)$itemData['slots'] : 0;
			if ($params['showslots'] && $slots > 0) {
				$html .= ' [' . $slots . ']';
			}
			
			$html .= '</span>';
		}
		
		// Fechar tag de tooltip se showdescription estiver habilitado
		if ($params['showdescription']) {
			$html .= '</span>';
		}
		
		return [
			$html,
			'noparse' => true,
			'isHTML' => true,
			'markerType' => 'nowiki'
		];
	}
	
	/**
	 * Busca informações do item a partir da API
	 *
	 * @param string $itemId ID do item
	 * @param bool $useCache Se deve usar cache
	 * @param Parser $parser Parser object for cache
	 * @return array|null Dados do item ou null se não encontrado
	 */
	private static function fetchItemData($itemId, $useCache = true, $parser = null) {
		// Usar o sistema de cache do Parser em vez de wgMemc
		static $itemCache = [];
		
		// Cache em memória estática (dentro da execução atual)
		if (isset($itemCache[$itemId])) {
			return $itemCache[$itemId];
		}
		
		// Cache do Parser
		if ($useCache && $parser) {
			$cacheKey = 'ethelro-item-' . $itemId;
			$cachedData = $parser->getOutput()->getExtensionData($cacheKey);
			if ($cachedData !== null) {
				$itemCache[$itemId] = $cachedData;
				return $cachedData;
			}
		}
		
		// URL da API
		$apiUrl = 'https://api.ethelro.com/items/' . $itemId;
		
		// Fazer a requisição HTTP
		$options = [
			'timeout' => 5,
			'followRedirects' => true
		];
		
		$req = MediaWiki\MediaWikiServices::getInstance()->getHttpRequestFactory()
			->create($apiUrl, $options, __METHOD__);
		$status = $req->execute();
		
		if (!$status->isOK()) {
			wfDebugLog('itemdescription', "Erro ao buscar item #$itemId: " . $status->getMessage());
			return null;
		}
		
		// Decodificar o resultado
		$responseContent = $req->getContent();
		$data = json_decode($responseContent, true);
		
		if (!$data || !isset($data['id'])) {
			wfDebugLog('itemdescription', "Resposta inválida para o item #$itemId: $responseContent");
			return null;
		}
		
		// Guardar no cache
		$itemCache[$itemId] = $data;
		
		// Guardar no cache do Parser
		if ($useCache && $parser) {
			$cacheKey = 'ethelro-item-' . $itemId;
			$parser->getOutput()->setExtensionData($cacheKey, $data);
		}
		
		return $data;
	}
	
	/**
	 * Converte uma string em booleano
	 *
	 * @param string $value
	 * @return bool
	 */
	private static function parseBoolean($value) {
		if (!$value) {
			return false;
		}
		
		$value = strtolower(trim($value));
		
		// Valores considerados verdadeiros
		return in_array($value, ['true', 'yes', 'y', '1', 'on'], true);
	}
} 