module.exports = {
  apps: [
    {
      name: 'whatsapp-gateway',
      script: 'server.js',
      cwd: __dirname,
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '300M',
      env: {
        NODE_ENV: 'production',
        PORT: 3001
      },
      error_file: '../storage/logs/whatsapp-error.log',
      out_file: '../storage/logs/whatsapp-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    }
  ]
};
