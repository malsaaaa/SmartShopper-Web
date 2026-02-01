#!/usr/bin/env node

/**
 * Firecrawl MCP Client (Node.js)
 * Provides CLI interface to Firecrawl MCP Server
 */

const fs = require('fs');
const path = require('path');
const http = require('http');

// Configuration
const config = {
  serverHost: process.env.FIRECRAWL_SERVER_HOST || 'localhost',
  serverPort: process.env.FIRECRAWL_SERVER_PORT || 3000,
  apiKey: process.env.FIRECRAWL_API_KEY || '',
  debug: process.env.FIRECRAWL_DEBUG === 'true'
};

class FirecrawlMCPClient {
  constructor(options = {}) {
    this.host = options.host || config.serverHost;
    this.port = options.port || config.serverPort;
    this.apiKey = options.apiKey || config.apiKey;
    this.debug = options.debug || config.debug;
  }

  log(message) {
    if (this.debug) {
      console.log(`[DEBUG] ${message}`);
    }
  }

  async sendRequest(toolName, params) {
    return new Promise((resolve, reject) => {
      const data = JSON.stringify({
        method: 'tool_call',
        tool: toolName,
        params: params
      });

      const options = {
        hostname: this.host,
        port: this.port,
        path: '/tool',
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Content-Length': Buffer.byteLength(data),
          'Authorization': `Bearer ${this.apiKey}`
        }
      };

      this.log(`Sending request to ${toolName}`);

      const req = http.request(options, (res) => {
        let responseData = '';

        res.on('data', (chunk) => {
          responseData += chunk;
        });

        res.on('end', () => {
          try {
            const result = JSON.parse(responseData);
            resolve(result);
          } catch (e) {
            reject(new Error(`Failed to parse response: ${e.message}`));
          }
        });
      });

      req.on('error', (e) => {
        reject(e);
      });

      req.write(data);
      req.end();
    });
  }

  async crawl(url, options = {}) {
    const params = {
      url,
      depth: options.depth || 1,
      include_markdown: options.includeMarkdown !== false,
      timeout: options.timeout || 30
    };

    this.log(`Crawling: ${url}`);
    return this.sendRequest('crawl', params);
  }

  async scrape(url, schema, options = {}) {
    const params = {
      url,
      schema: schema || null,
      timeout: options.timeout || 30
    };

    this.log(`Scraping: ${url}`);
    return this.sendRequest('scrape', params);
  }

  async extract(url, selectors, options = {}) {
    const params = {
      url,
      selectors: selectors || [],
      timeout: options.timeout || 30
    };

    this.log(`Extracting from: ${url}`);
    return this.sendRequest('extract', params);
  }
}

// CLI Implementation
async function main() {
  const args = process.argv.slice(2);
  
  if (args.length === 0) {
    console.log(`
Firecrawl MCP Client - Web Scraping Tool

Usage:
  firecrawl-client [command] [options]

Commands:
  crawl     Crawl a URL and extract all content
  scrape    Scrape a URL for structured data
  extract   Extract content using CSS selectors
  help      Show this help message

Options:
  --url <url>           URL to crawl/scrape (required)
  --depth <n>           Crawl depth (default: 1)
  --markdown             Include markdown conversion (default: true)
  --selectors <list>    CSS selectors (comma-separated)
  --schema <file>       JSON schema file for scraping
  --timeout <seconds>   Request timeout (default: 30)
  --debug               Enable debug mode
  --server <host>       Server host (default: localhost)
  --port <port>         Server port (default: 3000)
  --output <file>       Save output to file
  --pretty              Pretty print JSON output

Examples:
  firecrawl-client crawl --url https://example.com
  firecrawl-client extract --url https://example.com --selectors "h1,p,a"
  firecrawl-client scrape --url https://example.com --schema schema.json
    `);
    process.exit(0);
  }

  const command = args[0];
  const options = parseArguments(args.slice(1));

  if (!options.url && command !== 'help') {
    console.error('Error: --url is required');
    process.exit(1);
  }

  try {
    const client = new FirecrawlMCPClient({
      host: options.server || config.serverHost,
      port: options.port || config.serverPort,
      debug: options.debug || config.debug
    });

    let result;

    switch (command) {
      case 'crawl':
        result = await client.crawl(options.url, {
          depth: parseInt(options.depth) || 1,
          includeMarkdown: options.markdown !== 'false',
          timeout: parseInt(options.timeout) || 30
        });
        break;

      case 'extract':
        const selectors = options.selectors 
          ? options.selectors.split(',').map(s => s.trim())
          : [];
        result = await client.extract(options.url, selectors, {
          timeout: parseInt(options.timeout) || 30
        });
        break;

      case 'scrape':
        let schema = null;
        if (options.schema) {
          const schemaFile = path.resolve(options.schema);
          schema = JSON.parse(fs.readFileSync(schemaFile, 'utf-8'));
        }
        result = await client.scrape(options.url, schema, {
          timeout: parseInt(options.timeout) || 30
        });
        break;

      case 'help':
        // Already printed above
        break;

      default:
        console.error(`Unknown command: ${command}`);
        process.exit(1);
    }

    if (result) {
      const output = options.pretty
        ? JSON.stringify(result, null, 2)
        : JSON.stringify(result);

      if (options.output) {
        fs.writeFileSync(path.resolve(options.output), output);
        console.log(`Output saved to: ${options.output}`);
      } else {
        console.log(output);
      }
    }

  } catch (error) {
    console.error(`Error: ${error.message}`);
    if (options.debug) {
      console.error(error.stack);
    }
    process.exit(1);
  }
}

function parseArguments(args) {
  const options = {};
  for (let i = 0; i < args.length; i++) {
    if (args[i].startsWith('--')) {
      const key = args[i].substring(2);
      const value = args[i + 1];
      
      if (value && !value.startsWith('--')) {
        options[key] = value;
        i++;
      } else {
        options[key] = true;
      }
    }
  }
  return options;
}

if (require.main === module) {
  main().catch(error => {
    console.error(`Fatal error: ${error.message}`);
    process.exit(1);
  });
}

module.exports = FirecrawlMCPClient;
