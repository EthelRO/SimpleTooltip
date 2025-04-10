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
		$parser->setFunctionHook( 'tip-item', [ __CLASS__, 'itemTooltip' ] );
		
		// Register new item parser function
		$parser->setFunctionHook( 'item', [ __CLASS__, 'itemDisplay' ] );
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
	
	/**
	 * Parser function handler for {{#item: id | param1=value1 | ... }}
	 * Displays an item icon and optionally name and description
	 *
	 * @param Parser $parser
	 * @param string $itemId
	 * @return array
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
		
		// Valores padrão dos parâmetros
		$params['showname'] = false;
		$params['showdescription'] = false;
		$params['icon_width'] = 24; // Largura padrão para o ícone
		$params['cache'] = true; // Cache habilitado por padrão
		
		// Processar argumentos nomeados
		foreach ($args as $arg) {
			$pair = explode('=', $arg, 2);
			if (count($pair) == 2) {
				$key = strtolower(trim($pair[0]));
				$value = trim($pair[1]);
				
				if ($key == 'showname' || $key == 'name' || $key == 'show_name') {
					$params['showname'] = self::parseBoolean($value);
				} elseif ($key == 'showdescription' || $key == 'description' || $key == 'show_description') {
					$params['showdescription'] = self::parseBoolean($value);
				} elseif ($key == 'width' || $key == 'icon_width') {
					$params['icon_width'] = (int)$value;
				} elseif ($key == 'cache') {
					$params['cache'] = self::parseBoolean($value);
				}
			}
		}
		
		// URL do ícone
		$iconUrl = 'https://assets.ethelro.com/item/' . $itemId . '.png';
		
		// Inicializar HTML
		$html = '';
		
		// Buscar dados do item se showname ou showdescription estiverem habilitados
		$itemData = null;
		if ($params['showname'] || $params['showdescription']) {
			$itemData = self::fetchItemData($itemId, $params['cache']);
			
			// Verificar se o item existe
			if (!$itemData) {
				return ['<span class="error">Item não encontrado: ' . htmlspecialchars($itemId) . '</span>'];
			}
		}
		
		// Se showdescription estiver habilitado, adicionamos o tooltip
		if ($params['showdescription'] && $itemData) {
			// Preparar dados para o tooltip
			$tooltipData = [
				'id' => $itemData['id'],
				'name' => $itemData['name'],
				'description' => $itemData['description'],
				'imageUrl' => $iconUrl,
				'slots' => $itemData['slots'] ?? 0
			];
			
			// Codificar para JSON
			$jsonData = json_encode($tooltipData);
			$jsonAttr = htmlspecialchars($jsonData, ENT_QUOTES, 'UTF-8');
			
			// Iniciar o elemento com tooltip
			$html .= '<span class="ethelro-item-tooltip" data-item-tooltip="' . $jsonAttr . '">';
		}
		
		// Adicionar imagem do item
		$html .= '<img src="' . htmlspecialchars($iconUrl) . '" alt="Item #' . htmlspecialchars($itemId) . '" ';
		$html .= 'class="ethelro-item-icon" width="' . (int)$params['icon_width'] . '" height="' . (int)$params['icon_width'] . '"';
		$html .= ' style="vertical-align: middle;"';
		$html .= '>';
		
		// Adicionar nome se showname estiver habilitado
		if ($params['showname'] && $itemData) {
			$html .= ' <span class="ethelro-item-name">' . htmlspecialchars($itemData['name']) . '</span>';
		}
		
		// Fechar tag de tooltip se showdescription estiver habilitado
		if ($params['showdescription'] && $itemData) {
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
	 * @return array|null Dados do item ou null se não encontrado
	 */
	private static function fetchItemData($itemId, $useCache = true) {
		global $wgMemc;
		
		// Chave de cache
		$cacheKey = wfMemcKey('ethelro-item', $itemId);
		
		// Verificar cache
		if ($useCache) {
			$cached = $wgMemc->get($cacheKey);
			if ($cached !== false) {
				return $cached;
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
		
		// Guardar no cache por 1 hora
		if ($useCache) {
			$wgMemc->set($cacheKey, $data, 3600);
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