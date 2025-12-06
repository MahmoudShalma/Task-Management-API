# Task Management System API

A robust and scalable RESTful API for managing tasks with role-based access control, built with Laravel 12 and Laravel Sanctum for stateless authentication.

## Features

- **Authentication**: Stateless API authentication using Laravel Sanctum
- **Role-Based Access Control**: Two user roles (Manager and User) with different permissions
- **Task Management**: Complete CRUD operations for tasks
- **Task Dependencies**: Support for task dependencies with validation
- **Filtering**: Filter tasks by status, due date range, and assigned user
- **Data Validation**: Comprehensive input validation and error handling
- **RESTful Design**: Following REST principles and best practices

## Requirements

- PHP >= 8.2
- Composer
- SQLite or MySQL database

## Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/MahmoudShalma/Task-Management-API
cd Task-Management-API
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
```

Edit the `.env` file and configure your database settings. By default, it uses SQLite:

```env
DB_CONNECTION=sqlite
```

For MySQL, update the following:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Create Database

For SQLite:
```bash
touch database/database.sqlite
```

For MySQL:
```bash
mysql -u root -p
CREATE DATABASE task_management;
exit;
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Seed Database

This will create test users (2 managers and 3 regular users):

```bash
php artisan db:seed
```

**Default Users:**
- Manager 1: `manager1@example.com` / `password123`
- Manager 2: `manager2@example.com` / `password123`
- User 1: `user1@example.com` / `password123`
- User 2: `user2@example.com` / `password123`
- User 3: `user3@example.com` / `password123`

### 8. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## API Endpoints

### Authentication

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| POST | `/api/login` | Login and get access token | Public |
| POST | `/api/logout` | Logout and revoke token | Authenticated |
| GET | `/api/me` | Get authenticated user info | Authenticated |

### Tasks

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/api/tasks` | List all tasks (filtered for users) | Authenticated |
| POST | `/api/tasks` | Create a new task | Manager only |
| GET | `/api/tasks/{id}` | Get task details | Authenticated |
| PUT/PATCH | `/api/tasks/{id}` | Update task | Manager (full) / User (status only) |
| DELETE | `/api/tasks/{id}` | Delete task | Manager only |

### Task Dependencies

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| POST | `/api/tasks/{id}/dependencies` | Add dependencies to task | Manager only |
| DELETE | `/api/tasks/{id}/dependencies` | Remove dependencies from task | Manager only |

## API Usage Examples

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "manager1@example.com",
    "password": "password123"
  }'
```

### Create Task (Manager)

```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Complete project documentation",
    "description": "Write comprehensive API documentation",
    "due_date": "2024-12-31",
    "assigned_to": 3
  }'
```

### Get All Tasks

```bash
curl -X GET "http://localhost:8000/api/tasks" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Filter Tasks

```bash
# By status
curl -X GET "http://localhost:8000/api/tasks?status=pending" \
  -H "Authorization: Bearer YOUR_TOKEN"

# By date range
curl -X GET "http://localhost:8000/api/tasks?due_date_from=2024-01-01&due_date_to=2024-12-31" \
  -H "Authorization: Bearer YOUR_TOKEN"

# By assigned user
curl -X GET "http://localhost:8000/api/tasks?assigned_to=3" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Update Task Status (User)

```bash
curl -X PATCH http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed"
  }'
```

### Add Task Dependencies (Manager)

```bash
curl -X POST http://localhost:8000/api/tasks/2/dependencies \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "dependencies": [1]
  }'
```

## Role Permissions

### Manager Role
- Create tasks
- Update all task fields (title, description, status, due date, assignee)
- Assign tasks to users
- Delete tasks
- Add/remove task dependencies
- View all tasks

### User Role
- View only tasks assigned to them
- Update only the status of tasks assigned to them
- Cannot create, delete, or reassign tasks

## Task Dependencies

- Tasks can depend on other tasks
- A task cannot be marked as completed until all its dependencies are completed
- Circular dependencies are prevented
- A task cannot depend on itself

## Database Schema (ERD)

### Users Table
- `id` (Primary Key)
- `name`
- `email` (Unique)
- `password`
- `role` (enum: 'manager', 'user')
- `timestamps`

### Tasks Table
- `id` (Primary Key)
- `title`
- `description`
- `status` (enum: 'pending', 'completed', 'canceled')
- `due_date`
- `assigned_to` (Foreign Key → users.id)
- `created_by` (Foreign Key → users.id)
- `timestamps`

### Task Dependencies Table
- `id` (Primary Key)
- `task_id` (Foreign Key → tasks.id)
- `depends_on_task_id` (Foreign Key → tasks.id)
- `timestamps`
- Unique constraint on (`task_id`, `depends_on_task_id`)

## Error Handling

The API returns appropriate HTTP status codes:

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Testing

### Using Postman

Import the Postman collection (`Task_Management_API.postman_collection.json`) included in the repository to test all endpoints.

1. Open Postman
2. Click "Import" button
3. Select the `Task_Management_API.postman_collection.json` file
4. The collection will be imported with all endpoints
5. Use the "Login - Manager" or "Login - User" request to get an access token
6. The token will be automatically saved to the collection variable and used in subsequent requests

## Security Features

- Stateless authentication using Laravel Sanctum
- Password hashing using bcrypt
- Input validation and sanitization
- SQL injection prevention through Eloquent ORM
- Role-based authorization
- CORS configuration

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
