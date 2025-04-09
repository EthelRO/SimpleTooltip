# EthelRO Item Tooltip

This extension adds support for advanced item tooltips from EthelRO.

## Usage

To use the item tooltip feature, you need to use the `{{#item-tooltip:}}` or `{{#tip-item:}}` parser function with the following syntax:

```
{{#item-tooltip: display text | item-json-data }}
```

Where:
- `display text` is the text that will be shown on the page
- `item-json-data` is a JSON string containing the item data

## Item JSON Format

The item data should be provided as a JSON string with the following properties:

```json
{
  "id": 1101,
  "name": "Sword",
  "refine": 7,
  "enchantgrade": 2,
  "description": "A basic sword",
  "imageUrl": "https://example.com/sword.png",
  "slots": 4,
  "cards": [
    {
      "id": 4001,
      "name": "Poring Card",
      "description": "Increases DEF by 20"
    }
  ]
}
```

Properties:
- `id`: The item ID (required)
- `name`: The item name (required)
- `refine`: The refine level (optional)
- `enchantgrade`: The enchant grade (0-4, optional)
- `description`: The item description (optional)
- `imageUrl`: The URL to the item image (optional)
- `slots`: Number of card slots (0-4, optional)
- `cards`: Array of card objects (optional)

## Example

```
{{#tip-item: +7 Sword | {"id": 1101, "name": "Sword", "refine": 7, "enchantgrade": 2, "description": "A basic sword with ^ff0000attack power +150^000000", "imageUrl": "https://example.com/sword.png"} }}
```

This will display "+7 Sword" on the page, and when hovered, will show a detailed tooltip with the item information.

## Styling

The tooltip uses custom styling from EthelRO, including enchant grade colors:
- Grade 1: Green
- Grade 2: Blue
- Grade 3: Purple
- Grade 4: Gold

## Text Formatting

The description supports special text formatting:
- `^RRGGBB` - Changes text color to the specified hex color
- Line breaks in the description are automatically converted to `<br>` tags

## Notes

- For inline tooltips with simple text, use the standard SimpleTooltip functions
- This tooltip type is designed specifically for game items with detailed properties 