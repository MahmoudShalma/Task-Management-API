# Entity Relationship Diagram (ERD)

## Database Schema for Task Management System

```
┌─────────────────────────────────────────┐
│              USERS                       │
├─────────────────────────────────────────┤
│ PK │ id            BIGINT UNSIGNED      │
│    │ name          VARCHAR(255)         │
│    │ email         VARCHAR(255) UNIQUE  │
│    │ password      VARCHAR(255)         │
│    │ role          ENUM('manager','user')│
│    │ remember_token VARCHAR(100)        │
│    │ created_at    TIMESTAMP            │
│    │ updated_at    TIMESTAMP            │
└─────────────────────────────────────────┘
           │                    │
           │                    │
           │ 1                  │ 1
           │                    │
           │                    │
           │ N                  │ N
           ▼                    ▼
┌─────────────────────────────────────────┐
│              TASKS                       │
├─────────────────────────────────────────┤
│ PK │ id            BIGINT UNSIGNED      │
│    │ title         VARCHAR(255)         │
│    │ description   TEXT                 │
│    │ status        ENUM('pending',      │
│    │               'completed',         │
│    │               'canceled')          │
│    │ due_date      DATE                 │
│ FK │ assigned_to   BIGINT UNSIGNED      │
│ FK │ created_by    BIGINT UNSIGNED      │
│    │ created_at    TIMESTAMP            │
│    │ updated_at    TIMESTAMP            │
└─────────────────────────────────────────┘
           │
           │
           │ N
           │
           ▼
┌─────────────────────────────────────────┐
│         TASK_DEPENDENCIES                │
├─────────────────────────────────────────┤
│ PK │ id                BIGINT UNSIGNED  │
│ FK │ task_id           BIGINT UNSIGNED  │
│ FK │ depends_on_task_id BIGINT UNSIGNED │
│    │ created_at        TIMESTAMP        │
│    │ updated_at        TIMESTAMP        │
│    │                                    │
│    │ UNIQUE(task_id, depends_on_task_id)│
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│    PERSONAL_ACCESS_TOKENS (Sanctum)     │
├─────────────────────────────────────────┤
│ PK │ id            BIGINT UNSIGNED      │
│    │ tokenable_type VARCHAR(255)        │
│ FK │ tokenable_id  BIGINT UNSIGNED      │
│    │ name          VARCHAR(255)         │
│    │ token         VARCHAR(64) UNIQUE   │
│    │ abilities     TEXT                 │
│    │ last_used_at  TIMESTAMP            │
│    │ expires_at    TIMESTAMP            │
│    │ created_at    TIMESTAMP            │
│    │ updated_at    TIMESTAMP            │
└─────────────────────────────────────────┘
```

## Relationships

### USERS → TASKS
- **One-to-Many (as assignee)**: A user can be assigned to multiple tasks
  - `users.id` → `tasks.assigned_to`
  - A task can be assigned to zero or one user
  - Constraint: ON DELETE SET NULL

- **One-to-Many (as creator)**: A user (manager) can create multiple tasks
  - `users.id` → `tasks.created_by`
  - A task must have one creator
  - Constraint: ON DELETE SET NULL

### TASKS → TASK_DEPENDENCIES
- **Many-to-Many (self-referential)**: Tasks can depend on other tasks
  - `tasks.id` → `task_dependencies.task_id`
  - `tasks.id` → `task_dependencies.depends_on_task_id`
  - A task can have multiple dependencies
  - A task can be a dependency for multiple other tasks
  - Constraint: ON DELETE CASCADE

### USERS → PERSONAL_ACCESS_TOKENS
- **Polymorphic One-to-Many**: A user can have multiple API tokens
  - `users.id` → `personal_access_tokens.tokenable_id`
  - Used by Laravel Sanctum for API authentication

## Business Rules

1. **User Roles**:
   - `manager`: Can create, update, delete tasks and manage dependencies
   - `user`: Can only view and update status of tasks assigned to them

2. **Task Status**:
   - `pending`: Initial status when task is created
   - `completed`: Task is finished (requires all dependencies to be completed)
   - `canceled`: Task is cancelled

3. **Task Dependencies**:
   - A task cannot be completed until all its dependencies are completed
   - Circular dependencies are prevented
   - A task cannot depend on itself

4. **Authorization**:
   - Managers can perform all operations
   - Users can only:
     - View tasks assigned to them
     - Update status of tasks assigned to them

## Indexes

- `users.email` - UNIQUE index for authentication
- `tasks.assigned_to` - Foreign key index for filtering
- `tasks.created_by` - Foreign key index
- `tasks.status` - Index for filtering tasks by status
- `tasks.due_date` - Index for filtering tasks by due date
- `task_dependencies(task_id, depends_on_task_id)` - Unique composite index
- `personal_access_tokens.token` - Unique index for token lookup
