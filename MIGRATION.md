# Migração do SimpleTooltip para ItemDescription

Este documento fornece instruções para migrar do SimpleTooltip para o ItemDescription.

## Passo 1: Remover a extensão SimpleTooltip

No seu arquivo `LocalSettings.php`, remova a linha:

```php
wfLoadExtension( 'SimpleTooltip' );
```

## Passo 2: Instalar o ItemDescription

1. Faça o download do código e coloque-o no diretório `extensions/ItemDescription` do seu MediaWiki.
2. Adicione a seguinte linha ao seu arquivo `LocalSettings.php`:

```php
wfLoadExtension( 'ItemDescription' );
```

## Passo 3: Atualizar as tags nas páginas

Substitua todas as ocorrências dos parser functions antigos pelos novos:

### Tags a serem removidas
- `{{#simple-tooltip:}}`
- `{{#tip-text:}}`
- `{{#simple-tooltip-info:}}`
- `{{#tip-info:}}`
- `{{#simple-tooltip-img:}}`
- `{{#tip-img:}}`

### Tags a serem utilizadas
- `{{#item-tooltip:}}` ou `{{#tip-item:}}`

## Passo 4: Verificando a instalação

Após a instalação, você pode verificar se tudo está funcionando corretamente acessando uma página com o tooltip ativado:

```
{{#tip-item:+7 Espada|{"id": 1101, "name": "Espada", "refine": 7, "enchantgrade": 2, "description": "Uma espada básica com ^ff0000poder de ataque +150^000000", "imageUrl": "https://exemplo.com/imagem.png"}}}
```

Se precisar depurar, adicione `?itemdescription-debug=1` ao final da URL. 