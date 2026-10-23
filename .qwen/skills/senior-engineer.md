# Senior Software Engineer Skill

Provides architectural guidance, code review standards, best practices, and engineering leadership principles.

## Code Quality Standards

### SOLID Principles

**S - Single Responsibility**
```php
// ❌ Bad: Multiple responsibilities
class UserService {
    public function register($data) {
        // Validation
        // Database insert
        // Email sending
        // Logging
    }
}

// ✅ Good: Single responsibility
class UserValidator { /* Validation only */ }
class UserRepository { /* Database only */ }
class UserEmailService { /* Emails only */ }
```

**O - Open/Closed**
```php
// ✅ Open for extension, closed for modification
interface PaymentProcessor {
    public function process(float $amount): bool;
}

class StripeProcessor implements PaymentProcessor { /* ... */ }
class PayPalProcessor implements PaymentProcessor { /* ... */ }
```

**L - Liskov Substitution**
```php
// ✅ Subtypes must be substitutable
abstract class Notification {
    abstract public function send(string $to, string $message): bool;
}

class EmailNotification extends Notification { /* ... */ }
class SmsNotification extends Notification { /* ... */ }
```

**I - Interface Segregation**
```php
// ❌ Bad: Fat interface
interface Worker {
    public function work();
    public function eat();
    public function sleep();
}

// ✅ Good: Segregated interfaces
interface Workable { public function work(); }
interface Feedable { public function eat(); }
```

**D - Dependency Inversion**
```php
// ✅ Depend on abstractions
class UserController {
    public function __construct(
        private UserRepositoryInterface $users
    ) {}
}
```

## Architecture Patterns

### Repository Pattern
```php
interface UserRepositoryInterface {
    public function find(int $id): ?User;
    public function create(array $data): User;
    public function update(int $id, array $data): bool;
}

class EloquentUserRepository implements UserRepositoryInterface {
    public function __construct(private User $model) {}
    
    public function find(int $id): ?User {
        return $this->model->with('roles')->find($id);
    }
}
```

### Service Layer
```php
class UserRegistrationService {
    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasher $hasher,
        private EventDispatcher $events
    ) {}
    
    public function register(RegisterUserDto $dto): User {
        DB::transaction(function () use ($dto) {
            $user = $this->users->create([
                'email' => $dto->email,
                'password' => $this->hasher->hash($dto->password),
            ]);
            
            $this->events->dispatch(new UserRegistered($user));
            
            return $user;
        });
    }
}
```

### DTOs (Data Transfer Objects)
```php
class RegisterUserDto {
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $name,
    ) {}
    
    public static function fromRequest(Request $request): self {
        return new self(
            email: $request->validated('email'),
            password: $request->validated('password'),
            name: $request->validated('name'),
        );
    }
}
```

## Database Best Practices

### Query Optimization
```php
// ❌ N+1 Problem
$users = User::all();
foreach ($users as $user) {
    echo $user->profile->name; // Query per user
}

// ✅ Eager loading
$users = User::with('profile')->get();

// ❌ Inefficient
$users = User::where('active', true)->get();
$count = $users->filter->isAdmin()->count();

// ✅ Efficient
$count = User::where('active', true)
    ->where('is_admin', true)
    ->count();
```

### Indexing Strategy
```php
Schema::table('users', function (Blueprint $table) {
    // Single column index
    $table->index('email');
    
    // Composite index (order matters)
    $table->index(['status', 'created_at']);
    
    // Unique index
    $table->unique('username');
});
```

### Transaction Management
```php
// ✅ Proper transaction handling
DB::transaction(function () use ($data) {
    $user = User::create($data['user']);
    $user->profile()->create($data['profile']);
    $user->roles()->attach($data['roles']);
    
    // Auto rollback on exception
});

// ✅ With explicit handling
try {
    DB::beginTransaction();
    // Operations...
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    Log::error($e->getMessage());
    throw $e;
}
```

## API Design

### RESTful Conventions
```php
// Resource naming
GET    /api/users          // List users
POST   /api/users          // Create user
GET    /api/users/{id}     // Get user
PUT    /api/users/{id}     // Update user
DELETE /api/users/{id}     // Delete user
GET    /api/users/{id}/posts // Get user's posts

// Response structure
{
    "data": { /* resource */ },
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100
    },
    "links": {
        "next": "/api/users?page=2"
    }
}
```

### API Resources
```php
class UserResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

## Error Handling

### Exception Handling
```php
class Handler extends ExceptionHandler {
    protected $dontFlash = [
        'password',
        'password_confirmation',
        'api_token',
    ];
    
    public function render($request, Throwable $e): Response {
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'error' => 'Resource not found',
                'code' => 'RESOURCE_NOT_FOUND'
            ], 404);
        }
        
        return parent::render($request, $e);
    }
}
```

### Custom Exceptions
```php
class InvalidUserInputException extends DomainException {
    public function __construct(
        public array $errors = []
    ) {
        parent::__construct('Invalid user input provided.');
    }
    
    public function toApiResponse(): array {
        return [
            'error' => $this->getMessage(),
            'validation_errors' => $this->errors,
        ];
    }
}
```

## Security Best Practices

### Input Validation
```php
class StoreUserRequest extends FormRequest {
    public function rules(): array {
        return [
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
```

### Authorization
```php
class UserPolicy {
    public function update(User $authUser, User $user): bool {
        return $authUser->id === $user->id 
            || $authUser->hasRole('admin');
    }
}

// Usage
Gate::authorize('update', $user);
```

### Rate Limiting
```php
// In RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by(
        $request->user()?->id ?: $request->ip()
    );
});
```

## Testing Standards

### Test Structure (AAA Pattern)
```php
public function test_user_can_be_created(): void {
    // Arrange
    $data = ['email' => 'test@example.com', 'password' => 'password'];
    
    // Act
    $response = $this->postJson('/api/users', $data);
    
    // Assert
    $response->assertStatus(201);
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
}
```

### Test Coverage Priorities
1. **Critical paths** (auth, payments, data mutations)
2. **Business logic** (services, domain models)
3. **Edge cases** (validation, error handling)
4. **Integration points** (APIs, external services)

## Code Review Checklist

### Functionality
- [ ] Does the code solve the right problem?
- [ ] Are edge cases handled?
- [ ] Is error handling adequate?

### Code Quality
- [ ] Is the code DRY (Don't Repeat Yourself)?
- [ ] Are functions/methods focused (SRP)?
- [ ] Are names descriptive and consistent?

### Performance
- [ ] Are there N+1 queries?
- [ ] Is caching used appropriately?
- [ ] Are database indexes considered?

### Security
- [ ] Is input validated?
- [ ] Is output escaped?
- [ ] Are auth checks in place?

### Maintainability
- [ ] Is the code testable?
- [ ] Is complexity reasonable?
- [ ] Is documentation needed?

## Documentation Standards

### PHPDoc
```php
/**
 * Register a new user account.
 *
 * @param RegisterUserDto $dto The registration data
 * @return User The created user
 * @throws \App\Exceptions\InvalidUserInputException
 * @throws \Illuminate\Database\UniqueConstraintViolationException
 */
public function register(RegisterUserDto $dto): User;
```

### README Sections
1. Installation & Setup
2. Configuration
3. Usage Examples
4. API Documentation
5. Testing
6. Deployment
7. Troubleshooting

## Performance Optimization

### Caching Strategy
```php
// Cache-aside pattern
$users = Cache::remember(
    "users.{$id}",
    now()->addHour(),
    fn() => User::with('profile')->find($id)
);

// Cache tags for invalidation
Cache::tags(['users', "user.{$id}"])->put($key, $value);
```

### Query Optimization
```php
// Use select() to fetch only needed columns
User::select('id', 'email', 'name')->get();

// Use chunk() for large datasets
User::chunk(200, function ($users) {
    foreach ($users as $user) {
        // Process...
    }
});
```

## Leadership & Mentorship

### Code Review Feedback
- **Be specific**: Point to exact lines
- **Be constructive**: Suggest alternatives
- **Be kind**: Use questions over commands
- **Prioritize**: Focus on critical issues first

### Technical Decision Records
Document significant decisions:
```markdown
# ADR-001: Database Choice

## Status
Accepted

## Context
Need a database for relational data with transactions.

## Decision
Use PostgreSQL 15 for ACID compliance and JSON support.

## Consequences
- Pros: Transactions, JSONB, full-text search
- Cons: More complex than SQLite for development
```
