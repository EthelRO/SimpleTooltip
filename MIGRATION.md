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
- `{{#item-tooltip:}}` ou `{{#tip-item:}}`

### Nova tag a ser utilizada
- `{{#item:ID_DO_ITEM}}` - Exibe o ícone do item, nome e descrição ao passar o mouse

#### Exemplos de uso da tag {{#item:}}
- `{{#item:502}}` - Mostra ícone, nome e descrição (tooltip)
- `{{#item:502|noname}}` ou `{{#item:502|nn}}` - Oculta o nome do item
- `{{#item:502|nodescription}}` ou `{{#item:502|nd}}` - Remove o tooltip
- `{{#item:502|noslots}}` ou `{{#item:502|ns}}` - Oculta informação de slots
- `{{#item:502|width=32}}` - Define um tamanho personalizado para o ícone
- `{{#item:502|noname|nodescription}}` - Mostra apenas o ícone

## Passo 4: Verificando a instalação

Após a instalação, você pode verificar se tudo está funcionando corretamente acessando uma página com exemplos:

```
{{#item:502}}
{{#item:1101}}
```

Se precisar depurar, adicione `?itemdescription-debug=1` ao final da URL. 