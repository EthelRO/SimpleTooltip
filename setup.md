# Configuração da extensão ItemDescription

Este documento contém os passos necessários para instalar e configurar a extensão ItemDescription.

## 1. Instalação limpa

Para uma instalação limpa, siga estes passos:

1. Faça o download do código fonte
2. Renomeie a pasta para `ItemDescription`
3. Coloque a pasta no diretório `extensions/` do seu MediaWiki
4. Adicione a seguinte linha ao seu arquivo `LocalSettings.php`:

```php
wfLoadExtension( 'ItemDescription' );
```

## 2. Estrutura de diretórios recomendada

```
ItemDescription/
├── docs/                     # Documentação
│   └── exemplo-tooltip.wiki  # Exemplos de uso
├── i18n/                     # Arquivos de internacionalização
│   ├── en.json               # Inglês
│   └── pt-br.json            # Português do Brasil
├── lib/                      # Bibliotecas JavaScript e CSS
│   ├── debug-helper.js       # Script de depuração
│   ├── ItemDescription.css   # Estilos dos tooltips
│   └── ItemDescription.js    # JavaScript principal
├── src/                      # Código PHP
│   └── ItemDescriptionHooks.php # Hooks do MediaWiki
├── composer.json             # Configurações do Composer
├── extension.json            # Configurações da extensão
├── ItemDescription.i18n.magic.php # Definições de magic words
├── LICENSE                   # Arquivo de licença
├── MIGRATION.md              # Guia de migração
└── README.md                 # Documentação principal
```

## 3. Testes e depuração

Para verificar se a extensão está funcionando corretamente:

1. Crie uma página com um exemplo de tooltip:
```
{{#tip-item:+7 Espada|{"id": 1101, "name": "Espada", "refine": 7, "enchantgrade": 2, "description": "Uma espada básica com ^ff0000poder de ataque +150^000000", "imageUrl": "https://exemplo.com/imagem.png"}}}
```

2. Para depurar problemas, adicione `?itemdescription-debug=1` ao final da URL da página.

3. Verifique o console do navegador (F12) para mensagens de diagnóstico.

## 4. Suporte

Para obter ajuda ou reportar problemas, visite:
https://github.com/ethelro/ItemDescription/issues 