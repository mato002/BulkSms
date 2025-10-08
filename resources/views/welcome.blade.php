<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bulk SMS Laravel</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #333; text-align: center; margin-bottom: 30px; }
            .status { background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4caf50; }
            .api-info { background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2196f3; }
            .endpoint { font-family: monospace; background: #f5f5f5; padding: 5px 10px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚀 Bulk SMS Laravel API</h1>
            
            <div class="status">
                <h3>✅ Application Status</h3>
                <p>Laravel application is running successfully!</p>
            </div>

            <div class="api-info">
                <h3>📡 API Endpoints</h3>
                <p><strong>Send SMS:</strong> <span class="endpoint">POST /api/sms/send</span></p>
                <p><strong>Get Balance:</strong> <span class="endpoint">GET /api/balance</span></p>
                <p><strong>Manage Contacts:</strong> <span class="endpoint">GET /api/contacts</span></p>
                <p><strong>Campaigns:</strong> <span class="endpoint">GET /api/campaigns</span></p>
            </div>

            <div class="status">
                <h3>🔧 Next Steps</h3>
                <ul>
                    <li>Run <code>composer install</code> to install dependencies</li>
                    <li>Run <code>php artisan key:generate</code> to generate app key</li>
                    <li>Configure your database in <code>.env</code></li>
                    <li>Run <code>php artisan migrate</code> to create tables</li>
                    <li>Set up your SMS gateway API keys</li>
                </ul>
            </div>
        </div>
    </body>
</html>



