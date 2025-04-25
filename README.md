# Stutter ORM

A lightweight, elegant ORM for PHP with intuitive relationship management, based on Laravel's Eloquent. Stutter is designed for educational purposes, making it easy for junior developers to understand how Eloquent works by using and reading its code.

## Installation

```bash
composer require drahil/stutter
```

## Basic Usage

### Connection Setup

```php
use drahil\Stutter\Core\ConnectionManager;

ConnectionManager::addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'your_database',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4'
]);
```

### Create a Model

```php
namespace App\Models;

use drahil\Stutter\Core\Model;

class User extends Model
{
    protected static string $table = 'users';
}
```

### Basic CRUD Operations

#### Retrieve All Records

```php
$allUsers = User::all();
```

#### Find a Record by ID

```php
$user = User::find(1);
```

#### Find or Fail

```php
try {
    $user = User::findOrFail(1);
} catch (\drahil\Stutter\Exceptions\ModelNotFoundException $e) {
    // Handle not found
}
```

#### Create a Record

```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

#### Update a Record

```php
$user = User::find(1);
$user->update([
    'name' => 'Jane Doe'
]);
```

#### Delete a Record

```php
$user = User::find(1);
$user->delete();
```

### Query Builder

#### Basic Where Clause

```php
$users = User::query()->where('active', true)->get();
```

#### Select Specific Columns

```php
$users = User::query()->select(['id', 'name'])->get();
```

#### Limit Results

```php
$users = User::query()->limit(10)->get();
```

#### Order Results

```php
$users = User::query()->orderBy('created_at', 'desc')->get();
```

#### Get First Result

```php
$user = User::query()->where('email', 'john@example.com')->first();
```

#### Count Results

```php
$count = User::query()->count();
```

#### Check Existence

```php
$exists = User::query()->where('email', 'john@example.com')->exists();
```

#### Where In Clause

```php
$users = User::query()->whereIn('id', [1, 2, 3])->get();
```

## Relationships

### Define Relationships

```php
// User model
public function profile()
{
    return $this->hasOne(Profile::class);
}

public function posts()
{
    return $this->hasMany(Post::class);
}

// Profile model
public function user()
{
    return $this->belongsTo(User::class);
}

// Post model
public function user()
{
    return $this->belongsTo(User::class);
}

public function tags()
{
    return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
}

// Tag model
public function posts()
{
    return $this->belongsToMany(Post::class, 'post_tag', 'tag_id', 'post_id');
}
```

### Using Relationships

#### One-to-One

```php
// Get a user's profile
$profile = User::find(1)->profile()->get();

// Get a profile's user
$user = Profile::find(1)->user()->get();
```

#### One-to-Many

```php
// Get a user's posts
$posts = User::find(1)->posts()->get();

// Get a post's user
$user = Post::find(1)->user()->get();
```

#### Many-to-Many

```php
// Get a post's tags
$tags = Post::find(1)->tags()->get();

// Get a tag's posts
$posts = Tag::find(1)->posts()->get();
```

## Full Example

Here's a complete example to demonstrate the library usage:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use drahil\Stutter\Core\ConnectionManager;
use App\Models\User;
use App\Models\Profile;
use App\Models\Post;
use App\Models\Tag;

// Set up database connection
ConnectionManager::addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'stutter_demo',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8mb4'
]);

// Basic CRUD operations
$allUsers = User::all();
$user = User::find(1);
$newUser = User::create(['name' => 'John Doe']);
$user->update(['name' => 'Jane Doe']);

// Query builder examples
$activeUsers = User::query()->where('active', true)->get();
$recentUsers = User::query()->orderBy('created_at', 'desc')->limit(5)->get();
$userCount = User::query()->count();

// Relationship examples
$userProfile = User::find(1)->profile()->get();
$userPosts = User::find(1)->posts()->get();
$postTags = Post::find(1)->tags()->get();
```

## Sample Database Schema

To try the examples, you can use the following schema:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE post_tag (
    post_id INT,
    tag_id INT,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id)
);

-- Insert sample data
INSERT INTO users (name) VALUES 
    ('Alice'),
    ('Bob'),
    ('Charlie');

INSERT INTO profiles (user_id, bio) VALUES
    (1, 'Alice bio'),
    (2, 'Bob bio'),
    (3, 'Charlie bio');

INSERT INTO posts (user_id, title, content) VALUES
    (1, 'Alice Post 1', 'Content 1'),
    (1, 'Alice Post 2', 'Content 2'),
    (2, 'Bob Post 1', 'Content 3');

INSERT INTO tags (name) VALUES
    ('PHP'),
    ('ORM'),
    ('Database');

INSERT INTO post_tag (post_id, tag_id) VALUES
    (1, 1),
    (1, 2),
    (2, 2),
    (2, 3),
    (3, 1);
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.