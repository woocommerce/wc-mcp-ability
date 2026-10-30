# WooCommerce MCP Ability Demo Plugin

This example registers `woocommerce-demo/store-info` with the WordPress Abilities API and exposes it through the shared [WordPress MCP Adapter](https://github.com/WordPress/mcp-adapter). It returns basic store information and, optionally, product, order, and customer counts. It does not change store data.

This is an educational example for a development store. The WooCommerce MCP integration is in developer preview and its APIs may change.

## Requirements

- WordPress 6.9 or later (includes the Abilities API)
- PHP 8.0 or later
- WooCommerce 11.3 or later
- The [MCP Adapter plugin](https://wordpress.org/plugins/mcp-adapter/) to make the ability available to MCP clients; WooCommerce does not bundle it

## Install

From the root of a WordPress development site:

```bash
git clone https://github.com/woocommerce/wc-mcp-ability.git wp-content/plugins/wc-mcp-ability
wp plugin install mcp-adapter --activate
wp plugin activate wc-mcp-ability
```

The demo plugin can register the ability without the MCP Adapter. The adapter is needed for MCP discovery and execution.

## Discover and run the ability

Connect an MCP client to the adapter's default server. For a local site, see the [WooCommerce MCP connection guide](https://developer.woocommerce.com/docs/features/mcp/#local-sites-using-stdio). The default server is `mcp-adapter-default-server` (HTTP route: `/wp-json/mcp/mcp-adapter-default-server`).

The server's `tools/list` response contains three adapter tools, not a separate `store-info` tool. Use `mcp-adapter-discover-abilities` to find `woocommerce-demo/store-info`, `mcp-adapter-get-ability-info` to inspect its schema, then `mcp-adapter-execute-ability` to run it.

For example, from the WordPress root, replace `admin` with a local user who has the `manage_woocommerce` capability. A normal MCP client performs the initialization handshake automatically; these manual CLI requests include it explicitly:

```bash
printf '%s\n' \
  '{"jsonrpc":"2.0","id":0,"method":"initialize","params":{"protocolVersion":"2025-11-25","capabilities":{},"clientInfo":{"name":"wc-mcp-demo","version":"1.0"}}}' \
  '{"jsonrpc":"2.0","method":"notifications/initialized"}' \
  '{"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"mcp-adapter-discover-abilities","arguments":{}}}' \
  '{"jsonrpc":"2.0","id":2,"method":"tools/call","params":{"name":"mcp-adapter-get-ability-info","arguments":{"ability_name":"woocommerce-demo/store-info"}}}' \
  '{"jsonrpc":"2.0","id":3,"method":"tools/call","params":{"name":"mcp-adapter-execute-ability","arguments":{"ability_name":"woocommerce-demo/store-info","parameters":{"include_stats":true}}}}' | \
  wp mcp-adapter serve --server=mcp-adapter-default-server --user=admin
```

Set `include_stats` to `false` or omit it to skip the counts. A successful result includes the store name, URL, admin email, WordPress and WooCommerce versions, currency, country, and `plugin_source`. With stats enabled, it also includes product, order, and customer counts. These counts are examples, not a full analytics report: product count covers published products, order count sums the seven standard statuses shown in `order_breakdown`, and customer count covers users with the `customer` role.

The adapter's default server requires an authenticated user with `read` access, and the ability itself requires `manage_woocommerce`. A user without that capability may discover the ability but cannot execute it. Store information includes the admin email, so use a dedicated WordPress user with only the capabilities needed by the MCP client.

## How it works

1. `wp_abilities_api_categories_init` registers the `woocommerce-demo` category.
2. `wp_abilities_api_init` registers `woocommerce-demo/store-info` in that category with input and output schemas, an execution callback, and a permission callback.
3. `meta.mcp.public = true` makes the ability discoverable through the MCP Adapter's default server; `meta.mcp.type = tool` identifies its MCP component type.

The removed `woocommerce_mcp_include_ability` filter and `/wp-json/woocommerce/mcp` endpoint are not used. See the [WooCommerce 11.3 migration guidance](https://developer.woocommerce.com/docs/features/mcp/#removed-in-woocommerce-113) and the [MCP Adapter ability guide](https://github.com/WordPress/mcp-adapter/blob/trunk/docs/guides/creating-abilities.md) for the current integration pattern.

## License

This demo plugin follows the same licensing as WooCommerce core.
