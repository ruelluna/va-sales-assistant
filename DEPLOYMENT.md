# Deployment Guide

## Environment Variables

### Laravel Application

Required environment variables for the Laravel application:

```env
APP_NAME="VA Call Assistant"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=va_call_assistant
DB_USERNAME=va_user
DB_PASSWORD=your_secure_password

REDIS_HOST=redis
REDIS_PORT=6379

TWILIO_ACCOUNT_SID=your_twilio_account_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_PHONE_NUMBER=+1234567890

OPENAI_API_KEY=your_openai_api_key

BROADCAST_CONNECTION=ably
ABLY_KEY=your_ably_api_key
VITE_ABLY_KEY=your_ably_api_key

MEDIA_STREAM_URL=wss://your-domain.com:8081/stream
```

### Media Stream Service

Required environment variables for the Python Media Stream Service:

```env
DEEPGRAM_API_KEY=your_deepgram_api_key
LARAVEL_API_URL=http://laravel:8000
LARAVEL_API_TOKEN=your_laravel_api_token
MEDIA_STREAM_PORT=8080
```

## Deployment Steps

1. **Clone the repository** to your server
2. **Copy environment file**: `cp .env.example .env`
3. **Generate application key**: `php artisan key:generate`
4. **Set up environment variables** in `.env` file
5. **Build and start containers**: `docker-compose up -d --build`
6. **Run migrations**: `docker-compose exec laravel php artisan migrate --force`
7. **Set up Ably**: Get your Ably API key from [Ably Dashboard](https://www.ably.com/) and add it to your `.env` file
8. **Set up SSL/TLS** (recommended for production) using a reverse proxy like Nginx or Traefik

## Port Mappings

- **80/443**: Nginx (HTTP/HTTPS)
- **8000**: Laravel application
- **3306**: MySQL
- **6379**: Redis
- **8080**: (Not used - Ably handles WebSockets)
- **8081**: Media Stream Service

## SSL/TLS Setup

For production, set up SSL/TLS certificates. You can use:
- Let's Encrypt with Certbot
- Cloudflare SSL
- Custom certificates

Update the Nginx configuration to handle HTTPS and WebSocket connections.

## Scaling

To scale the queue workers:

```bash
docker-compose up -d --scale queue=3
```

## Monitoring

Monitor the containers:

```bash
docker-compose ps
docker-compose logs -f laravel
docker-compose logs -f queue
docker-compose logs -f media-stream
```

## Backup

Backup the MySQL database:

```bash
docker-compose exec mysql mysqldump -u va_user -p va_call_assistant > backup.sql
```

## Troubleshooting

1. **Check container logs**: `docker-compose logs [service-name]`
2. **Restart services**: `docker-compose restart [service-name]`
3. **Rebuild containers**: `docker-compose up -d --build`
4. **Check network connectivity**: `docker-compose exec laravel ping mysql`
