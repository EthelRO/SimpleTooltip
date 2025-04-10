# ItemDescription

Extensão para MediaWiki que permite exibir tooltips detalhados para itens do EthelRO.

## Instalação

1. Faça o download do código e coloque-o no diretório `extensions/ItemDescription` do seu MediaWiki.
2. Adicione a seguinte linha ao seu arquivo `LocalSettings.php`:

```php
wfLoadExtension( 'ItemDescription' );
```

3. Pronto! A extensão está instalada.

## Uso

### Tooltip de Item

Use o parser function `{{#tip-item:}}` ou `{{#item-tooltip:}}` com a seguinte sintaxe:

```
{{#tip-item: texto a ser exibido | dados do item em JSON }}
```

Exemplo:

```
{{#tip-item:+7 Espada|{"id": 1101, "name": "Espada", "refine": 7, "enchantgrade": 2, "description": "Uma espada básica com ^ff0000poder de ataque +150^000000", "imageUrl": "https://exemplo.com/imagem.png"}}}
```

### Formato de Dados JSON

Os dados do item devem ser fornecidos no formato JSON com as seguintes propriedades:

```json
{
  "id": 1101,                // ID do item (obrigatório)
  "name": "Nome do Item",    // Nome do item (obrigatório)
  "refine": 7,               // Nível de refino (opcional)
  "enchantgrade": 2,         // Grau do encantamento (1-4, opcional)
  "description": "Descrição do item com ^ff0000texto colorido^000000",
  "imageUrl": "https://url-da-imagem.png",  // URL da imagem (opcional)
  "slots": 4,                // Número de slots (0-4, opcional)
  "cards": [                 // Array de cards (opcional)
    {
      "id": 4001,
      "name": "Nome do Card",
      "description": "Descrição do card"
    }
  ]
}
```

### Formatação de Texto

Você pode usar o formato `^RRGGBB` para mudar a cor do texto na descrição:

* `^ff0000` - Vermelho
* `^00ff00` - Verde
* `^0000ff` - Azul
* `^ffff00` - Amarelo
* `^000000` - Preto (para voltar ao normal)

### Cores dos Graus

* Grau 1: Verde
* Grau 2: Azul
* Grau 3: Roxo
* Grau 4: Dourado

## Depuração

Para ativar o modo de depuração, adicione `?itemdescription-debug=1` ao final da URL da página.

## Licença

Esta extensão é licenciada sob os termos da [Licença MIT](LICENSE). 