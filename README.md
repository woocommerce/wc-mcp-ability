# WooCommerce MCP Ability Demo Plugin

⚠️ **DEMO TOOL - NOT PRODUCTION READY** ⚠️

This is a demonstration plugin showing how third-party developers can integrate with the WordPress MCP (Model Context Protocol) server by registering custom WooCommerce abilities using the WordPress Abilities API. **This plugin is for educational and testing purposes only and should not be used in production environments.**

## Overview

This plugin demonstrates the integration between:
- [WordPress Abilities API](https://developer.wordpress.org/apis/abilities-api/) - A standardized way to register capabilities in WordPress
- [WordPress MCP Adapter](https://github.com/WordPress/mcp-adapter) - Exposes abilities through a shared MCP server when installed as a separate plugin

**Important Notice**: The MCP integration in WooCommerce is a **developer preview**. Implementation details, APIs, and integration patterns may change in future releases. This demo is designed to showcase current capabilities and may require updates as the feature matures.

The demo creates a custom ability that retrieves WooCommerce store information and statistics, showcasing how external plugins can extend the MCP server's capabilities.

## Requirements

- WordPress 7.0 or higher (includes the Abilities API)
- PHP 8.0 or higher
- WooCommerce 11.3 or higher
- [MCP Adapter plugin](https://wordpress.org/plugins/mcp-adapter/) for MCP access (not bundled with WooCommerce)

## Installation

1. Clone or download this repository to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/woocommerce/wc-mcp-ability.git wc-mcp-ability
   ```

2. Activate the plugin through the WordPress admin dashboard or WP-CLI:
   ```bash
   wp plugin activate wc-mcp-ability
   ```

3. Install and activate the MCP Adapter to expose the ability to MCP clients:
   ```bash
   wp plugin install mcp-adapter --activate
   ```

## What This Demo Does

The plugin registers a custom ability called `woocommerce-demo/store-info` that:

### Functionality
- Retrieves basic store information (name, URL, admin email, versions)
- Optionally includes store statistics (product count, order counts by status, customer count)
- Demonstrates proper schema definition for inputs and outputs
- Shows permission handling for MCP abilities

### Technical Implementation
- **Ability Registration**: Uses `wp_register_ability()` from the WordPress Abilities API
- **MCP Integration**: Registers a category and uses `meta.mcp.public` to expose the ability through the shared WordPress MCP Adapter
- **Permission Control**: Requires `manage_woocommerce` capability for access
- **Schema Definition**: Provides complete JSON schema for both input parameters and output structure

## Usage Example

Once installed, [connect an MCP client to the adapter's default server](https://developer.woocommerce.com/docs/features/mcp/#connecting-to-the-mcp-server). Discover `woocommerce-demo/store-info`, then call `mcp-adapter-execute-ability` with these arguments:

```json
{
  "ability_name": "woocommerce-demo/store-info",
  "parameters": {
    "include_stats": true
  }
}
```

### Sample Response

```json
{
  "store_name": "My WooCommerce Store",
  "store_url": "https://example.com",
  "admin_email": "admin@example.com",
  "woocommerce_version": "11.3.0",
  "wordpress_version": "7.0",
  "currency": "USD",
  "country": "US",
  "plugin_source": "WooCommerce Abilities Demo Plugin v1.1.0",
  "stats": {
    "product_count": 150,
    "order_count": 1250,
    "order_breakdown": {
      "completed": 1100,
      "processing": 50,
      "pending": 25,
      "on-hold": 15,
      "cancelled": 35,
      "refunded": 20,
      "failed": 5
    },
    "customer_count": 500
  }
}
```

## Key Integration Points

### 1. Ability Registration
```php
add_action( 'wp_abilities_api_categories_init', array( __CLASS__, 'register_ability_category' ) );
add_action( 'wp_abilities_api_init', array( __CLASS__, 'register_store_info_ability' ) );
```

The ability uses the registered `woocommerce-demo` category.

### 2. WordPress MCP Adapter Exposure
```php
'meta' => array(
    'mcp' => array(
        'public' => true,
        'type'   => 'tool',
    ),
),
```

**Note**: The `woocommerce_mcp_include_ability` filter and WooCommerce-specific MCP endpoint were removed in WooCommerce 11.3. The shared adapter's default server uses `meta.mcp.public` to discover this ability; its permission callback still controls execution.

### 3. Schema Definition
The plugin demonstrates proper schema definition for both input and output, enabling automatic validation and documentation generation.

## Development Guidelines

When creating your own abilities:

1. **Use Proper Namespacing**: Choose a unique namespace for your abilities (e.g., `your-plugin/ability-name`)
2. **Define Complete Schemas**: Provide thorough input and output schemas for better integration
3. **Implement Permission Checks**: Always include appropriate permission callbacks
4. **Handle MCP Exposure**: Set `meta.mcp.public` to `true` for abilities that should be available through the shared WordPress MCP Adapter
5. **Follow WordPress Coding Standards**: Maintain consistency with WordPress development practices

## Architecture

```
WordPress Abilities API
    ↓ (registers abilities)
WordPress MCP Adapter
    ↓ (exposes via MCP protocol)
MCP Server
    ↓ (serves to clients)
MCP Clients (AI assistants, tools, etc.)
```

## Related Resources

- [WordPress Abilities API Documentation](https://developer.wordpress.org/apis/abilities-api/)
- [WordPress MCP Adapter Repository](https://github.com/WordPress/mcp-adapter)
- [Model Context Protocol Specification](https://modelcontextprotocol.io/)
- [WooCommerce MCP Documentation](https://developer.woocommerce.com/docs/features/mcp/)

## License

This demo plugin follows the same licensing as WooCommerce core.

## Important Disclaimers

- **This is a demo tool**: Not intended for production use
- **Developer Preview**: The WooCommerce MCP integration is in developer preview status
- **API Changes**: Implementation details and APIs may change in future releases
- **MCP Server Specific**: The `meta.mcp.public` setting opts this ability into the shared WordPress MCP Adapter's default server

## Contributing

This is a demonstration plugin. For contributions to the underlying systems:
- WordPress Abilities API: See the [Abilities API documentation](https://developer.wordpress.org/apis/abilities-api/)
- WordPress MCP Adapter: See the [mcp-adapter repository](https://github.com/WordPress/mcp-adapter)
