# Documentação da API - Mr. Carlos Barbershop

## Índice
1. [Visão Geral](#visão-geral)
2. [Autenticação](#autenticação)
3. [Endpoints Públicos](#endpoints-públicos)
4. [Endpoints de Agendamento](#endpoints-de-agendamento)
5. [Endpoints do Barbeiro](#endpoints-do-barbeiro)
6. [Endpoints Administrativos](#endpoints-administrativos)
7. [Códigos de Erro](#códigos-de-erro)
8. [Exemplos de Integração](#exemplos-de-integração)
9. [Rate Limiting](#rate-limiting)
10. [Webhooks](#webhooks)

---

## Visão Geral

A API do Mr. Carlos Barbershop oferece acesso programático às principais funcionalidades do sistema através de endpoints RESTful.

### URL Base
```
https://seu-dominio.com/api/
```

### Formato de Dados
- **Request**: JSON ou Form Data
- **Response**: JSON
- **Encoding**: UTF-8
- **Date Format**: ISO 8601 (YYYY-MM-DD HH:MM:SS)

### Headers Obrigatórios
```http
Content-Type: application/json
Accept: application/json
User-Agent: NomeDoSeuApp/1.0
```

---

## Autenticação

### Tipos de Autenticação

#### 1. Session-based (Web)
Para interfaces web, usar cookies de sessão automáticos após login.

#### 2. API Key (Aplicações)
Para integrações externas, usar API key no header:

```http
Authorization: Bearer sua_api_key_aqui
```

#### 3. JWT Token (Móvel)
Para aplicações móveis, usar JWT no header:

```http
Authorization: JWT eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Obter API Key

**Endpoint**: `POST /api/auth/api-key`

**Request**:
```json
{
    "email": "admin@example.com",
    "password": "senha_admin",
    "app_name": "Minha Integração"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "api_key": "ak_1234567890abcdef...",
        "expires_at": "2024-12-31T23:59:59Z",
        "permissions": ["read", "write"]
    }
}
```

---

## Endpoints Públicos

### Listar Serviços

**Endpoint**: `GET /api/services`

**Parâmetros**:
- `active` (boolean): Apenas serviços ativos (default: true)
- `category` (string): Filtrar por categoria

**Request**:
```http
GET /api/services?active=true&category=cortes
```

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Corte Clássico",
            "description": "Corte tradicional com acabamento",
            "price": 45.00,
            "duration": 30,
            "category": "cortes",
            "active": true
        },
        {
            "id": 2,
            "name": "Barba Completa", 
            "description": "Barba completa com navalha",
            "price": 35.00,
            "duration": 30,
            "category": "barbas",
            "active": true
        }
    ],
    "meta": {
        "total": 2,
        "count": 2
    }
}
```

### Listar Barbeiros

**Endpoint**: `GET /api/barbers`

**Parâmetros**:
- `active` (boolean): Apenas barbeiros ativos
- `with_schedule` (boolean): Incluir horários disponíveis hoje

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Carlos Silva",
            "specialties": ["Cortes clássicos", "Barbas"],
            "rating": 4.9,
            "active": true,
            "available_today": true,
            "next_available": "2025-10-15T14:30:00Z"
        }
    ]
}
```

### Verificar Disponibilidade

**Endpoint**: `GET /api/availability`

**Parâmetros**:
- `date` (required): Data no formato YYYY-MM-DD
- `barber_id` (optional): ID do barbeiro específico
- `service_ids` (optional): IDs dos serviços (array)

**Request**:
```http
GET /api/availability?date=2025-10-15&barber_id=1&service_ids[]=1&service_ids[]=2
```

**Response**:
```json
{
    "success": true,
    "data": {
        "date": "2025-10-15",
        "barber": {
            "id": 1,
            "name": "Carlos Silva"
        },
        "services": [
            {"id": 1, "name": "Corte Clássico", "duration": 30},
            {"id": 2, "name": "Barba Completa", "duration": 30}
        ],
        "total_duration": 60,
        "available_slots": [
            "08:00:00",
            "08:30:00", 
            "09:00:00",
            "14:30:00",
            "15:00:00"
        ]
    }
}
```

---

## Endpoints de Agendamento

### Criar Agendamento

**Endpoint**: `POST /api/bookings`

**Autenticação**: Obrigatória (Cliente ou Admin)

**Request**:
```json
{
    "client_id": 123,
    "barber_id": 1,
    "services": [1, 2],
    "date": "2025-10-15",
    "time": "14:30",
    "notes": "Corte social, barba baixa"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 456,
        "client": {
            "id": 123,
            "name": "João Silva",
            "email": "joao@example.com",
            "phone": "(11) 99999-9999"
        },
        "barber": {
            "id": 1,
            "name": "Carlos Silva"
        },
        "services": [
            {
                "id": 1,
                "name": "Corte Clássico",
                "price": 45.00,
                "duration": 30
            },
            {
                "id": 2, 
                "name": "Barba Completa",
                "price": 35.00,
                "duration": 30
            }
        ],
        "date": "2025-10-15",
        "time": "14:30:00",
        "end_time": "15:30:00",
        "total_price": 80.00,
        "total_duration": 60,
        "status": "agendado",
        "notes": "Corte social, barba baixa",
        "created_at": "2025-10-14T10:30:00Z"
    }
}
```

### Listar Agendamentos

**Endpoint**: `GET /api/bookings`

**Autenticação**: Obrigatória

**Parâmetros**:
- `client_id` (int): Agendamentos de cliente específico
- `barber_id` (int): Agendamentos de barbeiro específico  
- `date_from` (date): Data inicial
- `date_to` (date): Data final
- `status` (string): Status do agendamento
- `page` (int): Página (pagination)
- `per_page` (int): Items por página (max: 100)

**Request**:
```http
GET /api/bookings?date_from=2025-10-01&date_to=2025-10-31&status=agendado&page=1&per_page=20
```

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 456,
            "client_name": "João Silva",
            "barber_name": "Carlos Silva", 
            "services_names": ["Corte Clássico", "Barba Completa"],
            "date": "2025-10-15",
            "time": "14:30:00",
            "total_price": 80.00,
            "status": "agendado"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 156,
        "total_pages": 8
    }
}
```

### Atualizar Agendamento

**Endpoint**: `PUT /api/bookings/{id}`

**Autenticação**: Obrigatória (Cliente proprietário ou Admin)

**Request**:
```json
{
    "date": "2025-10-16",
    "time": "15:00",
    "notes": "Alteração de horário solicitada"
}
```

**Response**:
```json
{
    "success": true,
    "message": "Agendamento atualizado com sucesso",
    "data": {
        "id": 456,
        "date": "2025-10-16", 
        "time": "15:00:00",
        "status": "reagendado"
    }
}
```

### Cancelar Agendamento

**Endpoint**: `DELETE /api/bookings/{id}`

**Autenticação**: Obrigatória (Cliente proprietário ou Admin)

**Request**:
```json
{
    "reason": "Imprevisto pessoal"
}
```

**Response**:
```json
{
    "success": true,
    "message": "Agendamento cancelado com sucesso",
    "data": {
        "id": 456,
        "status": "cancelado",
        "cancelled_at": "2025-10-14T10:45:00Z",
        "cancellation_reason": "Imprevisto pessoal"
    }
}
```

---

## Endpoints do Barbeiro

### Obter Agenda do Barbeiro

**Endpoint**: `GET /api/barber/schedule`

**Autenticação**: Barbeiro autenticado

**Parâmetros**:
- `date` (date): Data específica (default: hoje)
- `view` (string): 'day', 'week', 'month' (default: 'day')

**Response**:
```json
{
    "success": true,
    "data": {
        "barber": {
            "id": 1,
            "name": "Carlos Silva"
        },
        "date": "2025-10-15",
        "working_hours": {
            "start": "08:00:00",
            "end": "18:00:00", 
            "lunch_break": {
                "start": "12:00:00",
                "end": "13:00:00"
            }
        },
        "appointments": [
            {
                "id": 456,
                "client_name": "João Silva",
                "client_phone": "(11) 99999-9999",
                "services": ["Corte Clássico", "Barba Completa"],
                "start_time": "14:30:00",
                "end_time": "15:30:00",
                "total_price": 80.00,
                "status": "agendado",
                "notes": "Corte social, barba baixa"
            }
        ],
        "blocked_slots": [
            {
                "start_time": "16:00:00",
                "end_time": "17:00:00", 
                "reason": "Pausa pessoal"
            }
        ]
    }
}
```

### Bloquear/Desbloquear Horário

**Endpoint**: `POST /api/barber/block-time`

**Autenticação**: Barbeiro autenticado

**Request**:
```json
{
    "date": "2025-10-15",
    "start_time": "16:00",
    "end_time": "17:00",
    "reason": "Pausa pessoal",
    "action": "block"
}
```

**Response**:
```json
{
    "success": true,
    "message": "Horário bloqueado com sucesso",
    "data": {
        "id": 789,
        "date": "2025-10-15",
        "start_time": "16:00:00",
        "end_time": "17:00:00",
        "reason": "Pausa pessoal"
    }
}
```

### Atualizar Status do Barbeiro

**Endpoint**: `PUT /api/barber/status`

**Autenticação**: Barbeiro autenticado

**Request**:
```json
{
    "status": "disponivel"
}
```

**Valores válidos**: `disponivel`, `ocupado`, `indisponivel`

**Response**:
```json
{
    "success": true,
    "message": "Status atualizado",
    "data": {
        "status": "disponivel",
        "updated_at": "2025-10-14T10:50:00Z"
    }
}
```

---

## Endpoints Administrativos

### Dashboard Stats

**Endpoint**: `GET /api/admin/dashboard`

**Autenticação**: Admin obrigatória

**Parâmetros**:
- `period` (string): 'today', 'week', 'month', 'year'

**Response**:
```json
{
    "success": true,
    "data": {
        "period": "today",
        "date": "2025-10-15",
        "stats": {
            "appointments": {
                "total": 24,
                "confirmed": 22,
                "cancelled": 1,
                "no_show": 1
            },
            "revenue": {
                "total": 1560.00,
                "average_ticket": 65.00,
                "target": 1200.00,
                "achievement_percentage": 130.0
            },
            "clients": {
                "new_registrations": 3,
                "total_active": 856,
                "retention_rate": 68.7
            },
            "barbers": {
                "total": 4,
                "active": 4,
                "average_utilization": 87.0
            }
        }
    }
}
```

### Relatório Financeiro

**Endpoint**: `GET /api/admin/financial-report`

**Autenticação**: Admin obrigatória

**Parâmetros**:
- `date_from` (date): Data inicial
- `date_to` (date): Data final
- `group_by` (string): 'day', 'week', 'month'

**Response**:
```json
{
    "success": true,
    "data": {
        "period": {
            "from": "2025-10-01",
            "to": "2025-10-31"
        },
        "summary": {
            "total_revenue": 31890.00,
            "total_appointments": 486,
            "average_ticket": 65.62,
            "growth_percentage": 12.05
        },
        "by_service": [
            {
                "service_name": "Corte Clássico",
                "quantity": 245,
                "revenue": 11025.00,
                "percentage": 34.6
            },
            {
                "service_name": "Barba Completa", 
                "quantity": 156,
                "revenue": 5460.00,
                "percentage": 17.1
            }
        ],
        "by_barber": [
            {
                "barber_name": "Carlos Silva",
                "appointments": 128,
                "revenue": 8960.00,
                "commission": 5376.00
            }
        ],
        "daily_breakdown": [
            {
                "date": "2025-10-01",
                "appointments": 18,
                "revenue": 1260.00
            }
        ]
    }
}
```

### Gerenciar Serviços

**Endpoint**: `POST /api/admin/services`

**Autenticação**: Admin obrigatória

**Request**:
```json
{
    "name": "Novo Serviço",
    "description": "Descrição do serviço",
    "price": 75.00,
    "duration": 45,
    "category": "especiais",
    "active": true
}
```

**Response**:
```json
{
    "success": true,
    "message": "Serviço criado com sucesso",
    "data": {
        "id": 15,
        "name": "Novo Serviço",
        "price": 75.00,
        "duration": 45,
        "active": true,
        "created_at": "2025-10-14T11:00:00Z"
    }
}
```

---

## Códigos de Erro

### HTTP Status Codes

| Código | Significado | Descrição |
|--------|-------------|-----------|
| 200 | OK | Requisição bem-sucedida |
| 201 | Created | Recurso criado com sucesso |
| 400 | Bad Request | Dados inválidos na requisição |
| 401 | Unauthorized | Autenticação necessária |
| 403 | Forbidden | Permissão insuficiente |
| 404 | Not Found | Recurso não encontrado |
| 409 | Conflict | Conflito (ex: horário ocupado) |
| 422 | Unprocessable Entity | Dados válidos mas regra de negócio violada |
| 429 | Too Many Requests | Rate limit excedido |
| 500 | Internal Server Error | Erro interno do servidor |

### Formato de Erro

```json
{
    "success": false,
    "error": {
        "code": "INVALID_DATE",
        "message": "A data deve ser futura",
        "details": {
            "field": "date",
            "value": "2025-10-01",
            "min_date": "2025-10-15"
        }
    }
}
```

### Códigos de Erro Específicos

| Código | Descrição |
|--------|-----------|
| `INVALID_DATE` | Data inválida ou no passado |
| `BARBER_UNAVAILABLE` | Barbeiro não disponível no horário |
| `TIME_SLOT_OCCUPIED` | Horário já ocupado |
| `SERVICE_INACTIVE` | Serviço não está ativo |
| `BOOKING_NOT_FOUND` | Agendamento não encontrado |
| `PERMISSION_DENIED` | Permissão negada para a ação |
| `INVALID_TIME_RANGE` | Fora do horário de funcionamento |
| `BOOKING_TOO_LATE` | Muito tarde para alterar/cancelar |
| `RATE_LIMIT_EXCEEDED` | Muitas requisições |
| `API_KEY_INVALID` | Chave da API inválida |

---

## Rate Limiting

### Limites por Tipo de Usuário

| Tipo | Requisições por Minuto |
|------|------------------------|
| Público | 60 |
| Cliente Autenticado | 120 |
| Barbeiro | 240 |
| Admin | 600 |
| API Key | 1000 |

### Headers de Rate Limit

```http
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 87
X-RateLimit-Reset: 1634567890
```

### Resposta quando Limite Excedido

```json
{
    "success": false,
    "error": {
        "code": "RATE_LIMIT_EXCEEDED",
        "message": "Muitas requisições. Tente novamente em 43 segundos.",
        "retry_after": 43
    }
}
```

---

## Webhooks

### Configuração

**Endpoint**: `POST /api/admin/webhooks`

**Request**:
```json
{
    "url": "https://seu-sistema.com/webhook",
    "events": ["booking.created", "booking.cancelled"],
    "secret": "sua_chave_secreta"
}
```

### Eventos Disponíveis

| Evento | Descrição |
|--------|-----------|
| `booking.created` | Novo agendamento criado |
| `booking.updated` | Agendamento alterado |
| `booking.cancelled` | Agendamento cancelado |
| `booking.completed` | Agendamento concluído |
| `booking.no_show` | Cliente não compareceu |
| `client.registered` | Novo cliente registrado |
| `barber.status_changed` | Status do barbeiro alterado |

### Formato do Payload

```json
{
    "event": "booking.created",
    "timestamp": "2025-10-14T11:15:00Z",
    "data": {
        "booking": {
            "id": 456,
            "client": {
                "id": 123,
                "name": "João Silva",
                "email": "joao@example.com"
            },
            "barber": {
                "id": 1, 
                "name": "Carlos Silva"
            },
            "services": [
                {
                    "id": 1,
                    "name": "Corte Clássico",
                    "price": 45.00
                }
            ],
            "date": "2025-10-15",
            "time": "14:30:00",
            "total_price": 45.00,
            "status": "agendado"
        }
    },
    "signature": "sha256=a1b2c3d4..."
}
```

### Validação de Assinatura

```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$secret = 'sua_chave_secreta';

$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($signature, $expected)) {
    http_response_code(401);
    exit('Invalid signature');
}
```

---

## Exemplos de Integração

### JavaScript (Fetch API)

```javascript
// Obter disponibilidade
async function getAvailability(date, barberId) {
    const response = await fetch(`/api/availability?date=${date}&barber_id=${barberId}`, {
        headers: {
            'Accept': 'application/json'
        }
    });
    
    const data = await response.json();
    
    if (data.success) {
        return data.data.available_slots;
    } else {
        throw new Error(data.error.message);
    }
}

// Criar agendamento
async function createBooking(bookingData) {
    const response = await fetch('/api/bookings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('api_key')
        },
        body: JSON.stringify(bookingData)
    });
    
    const data = await response.json();
    
    if (data.success) {
        return data.data;
    } else {
        throw new Error(data.error.message);
    }
}
```

### PHP (cURL)

```php
class BarberShopAPI 
{
    private $baseUrl;
    private $apiKey;
    
    public function __construct($baseUrl, $apiKey) 
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }
    
    private function request($method, $endpoint, $data = null) 
    {
        $url = $this->baseUrl . '/api' . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_CUSTOMREQUEST => $method
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 400) {
            throw new Exception($decoded['error']['message'] ?? 'API Error');
        }
        
        return $decoded;
    }
    
    public function getServices() 
    {
        return $this->request('GET', '/services');
    }
    
    public function createBooking($data) 
    {
        return $this->request('POST', '/bookings', $data);
    }
}

// Uso
$api = new BarberShopAPI('https://seu-dominio.com', 'sua_api_key');

try {
    $services = $api->getServices();
    echo "Serviços disponíveis: " . count($services['data']) . "\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
```

### Python (requests)

```python
import requests
from datetime import datetime

class BarberShopAPI:
    def __init__(self, base_url, api_key):
        self.base_url = base_url.rstrip('/')
        self.api_key = api_key
        self.session = requests.Session()
        self.session.headers.update({
            'Content-Type': 'application/json',
            'Accept': 'application/json', 
            'Authorization': f'Bearer {api_key}'
        })
    
    def get_availability(self, date, barber_id=None):
        params = {'date': date}
        if barber_id:
            params['barber_id'] = barber_id
            
        response = self.session.get(f'{self.base_url}/api/availability', params=params)
        return self._handle_response(response)
    
    def create_booking(self, booking_data):
        response = self.session.post(f'{self.base_url}/api/bookings', json=booking_data)
        return self._handle_response(response)
    
    def _handle_response(self, response):
        data = response.json()
        if response.status_code >= 400:
            raise Exception(data.get('error', {}).get('message', 'API Error'))
        return data

# Uso
api = BarberShopAPI('https://seu-dominio.com', 'sua_api_key')

try:
    availability = api.get_availability('2025-10-15', barber_id=1)
    print(f"Horários disponíveis: {availability['data']['available_slots']}")
except Exception as e:
    print(f"Erro: {e}")
```

---

## SDKs Oficiais

### JavaScript/Node.js
```bash
npm install mr-carlos-barbershop-sdk
```

### PHP
```bash
composer require mr-carlos/barbershop-sdk
```

### Python  
```bash
pip install mr-carlos-barbershop
```

### Documentação Interativa
- **Swagger UI**: `/api/docs`
- **Postman Collection**: [Download](/api/postman.json)
- **OpenAPI Spec**: [Download](/api/openapi.yaml)

---

*Documentação da API atualizada em: 14 de Outubro de 2025 - Versão 1.0*