# Testing Guide - SLSS Student Management System

## 🧪 Testing Overview

This guide covers manual testing, automated testing, and quality assurance for the SLSS Student Management System.

---

## 📋 Manual Testing Checklist

### 1. Authentication & Authorization

#### Login Tests
- [ ] **Valid Login**
  - Email: `admin@slss.edu.tt` / Password: `admin123`
  - Expected: Redirect to `/students`

- [ ] **Invalid Login**
  - Wrong password
  - Expected: Error message displayed

- [ ] **Role-Based Access**
  - Admin can access all features
  - Staff can create/edit but not delete
  - Viewer can only view records

- [ ] **Logout**
  - Click logout
  - Expected: Redirect to login, session cleared

#### Test Script:
```bash
# Test as different users
# Admin
curl -X POST http://localhost:8000/login \
  -d "email=admin@slss.edu.tt&password=admin123"

# Staff
curl -X POST http://localhost:8000/login \
  -d "email=staff@slss.edu.tt&password=staff123"

# Viewer
curl -X POST http://localhost:8000/login \
  -d "email=viewer@slss.edu.tt&password=viewer123"
```

---

### 2. Student Management (CRUD)

#### Create Student
- [ ] **Valid Data**
  - Fill all required fields
  - Upload photo (JPG, PNG < 2MB)
  - Expected: Student created, redirected to profile

- [ ] **Invalid Data**
  - Missing student name
  - Duplicate birth certificate PIN
  - Invalid photo format (PDF, EXE)
  - Photo too large (> 2MB)
  - Expected: Validation errors displayed

- [ ] **Photo Upload**
  - Upload valid image
  - Verify image appears in profile
  - Check storage path: `storage/app/public/passports/`

#### Read/View Student
- [ ] **View Single Student**
  - All fields displayed correctly
  - Photo displays or shows placeholder
  - Print button works
  - PDF button works

- [ ] **View Student List**
  - All students displayed
  - Pagination works (if > 100 students)
  - No PHP errors

#### Update Student
- [ ] **Edit Existing Student**
  - Update student name
  - Update photo
  - Expected: Changes saved, old photo deleted

- [ ] **Validation on Update**
  - Cannot make name empty
  - PIN uniqueness (except current student)
  - Photo validation applies

#### Delete Student
- [ ] **Soft Delete**
  - Admin can delete
  - Staff/Viewer cannot (403 error)
  - Student removed from list
  - Photo deleted from storage

- [ ] **Verify Soft Delete**
  ```sql
  SELECT * FROM students WHERE deleted_at IS NOT NULL;
  ```

---

### 3. Filtering & Search

#### Year Filter
- [ ] Select "All Years"
  - Expected: All students shown

- [ ] Select specific year (e.g., 2025)
  - Expected: Only 2025 registrations shown

#### Class Filter
- [ ] Select "All Students"
  - Expected: All students shown

- [ ] Select specific class (e.g., 1A)
  - Expected: Only Form 1A students shown

#### Name Filter
- [ ] Select "Student Name" (All)
  - Expected: All students shown

- [ ] Select specific student
  - Expected: Only that student shown

#### Combined Filters
- [ ] Year=2025 + Class=1A
  - Expected: Only 2025 Form 1A students

- [ ] All three filters
  - Expected: Intersection of all filters

#### Test Data:
```sql
-- Create test students
INSERT INTO students (student_name, form_1_class, registration_date, created_at, updated_at)
VALUES
  ('Test Student A', 'A', '2025-01-01', NOW(), NOW()),
  ('Test Student B', 'B', '2025-01-01', NOW(), NOW()),
  ('Test Student C', 'A', '2024-01-01', NOW(), NOW());
```

---

### 4. PDF Generation

#### Single Student PDF
- [ ] **Generate PDF**
  - Click "Generate PDF" button
  - Expected: PDF downloads with student name

- [ ] **PDF Content**
  - Watermark visible
  - All sections included
  - Photo displays (if exists)
  - Formatting correct
  - No layout breaks

#### Bulk PDF
- [ ] **No Filters**
  - Click "Bulk PDF"
  - Expected: All students in one PDF

- [ ] **With Filters**
  - Filter to specific class
  - Click "Bulk PDF"
  - Expected: Only filtered students in PDF

- [ ] **Large Dataset**
  - Generate PDF with 50+ students
  - Expected: No timeout, all students included

#### Print Functionality
- [ ] **Print Individual**
  - Click "Print" on student
  - Expected: Print dialog opens, formatted correctly

- [ ] **Print All**
  - Click "Print All"
  - Expected: Browser print dialog, all students included

---

### 5. CSV Import

#### Valid CSV Import
- [ ] **Standard Import**
  - Upload valid CSV with 5-10 students
  - Expected: Success message, students imported

- [ ] **Large Import**
  - Upload CSV with 100+ students
  - Expected: Completes without timeout

- [ ] **With Photos**
  - CSV with photo URLs
  - Expected: Photos downloaded/linked correctly

#### Invalid CSV Import
- [ ] **Missing Headers**
  - Upload CSV without header row
  - Expected: Error or skipped rows

- [ ] **Invalid Data**
  - Missing required fields
  - Invalid dates
  - Expected: Validation errors, rows skipped

- [ ] **Duplicate PINs**
  - CSV with duplicate birth certificate PIN
  - Expected: Duplicates skipped, count shown

#### Test CSV:
```csv
student_name,form_1_class,student_gender,student_dob,student_birth_certficate_pin
John Doe,A,Male,2012-01-15,1234567890
Jane Smith,B,Female,2012-03-20,0987654321
```

---

### 6. File Upload Security

#### Valid Uploads
- [ ] JPG image (< 2MB)
- [ ] PNG image (< 2MB)
- [ ] GIF image (< 2MB)
- [ ] WEBP image (< 2MB)

#### Invalid Uploads
- [ ] **Wrong Type**
  - PDF file
  - Expected: Validation error

- [ ] **Too Large**
  - 5MB image
  - Expected: Validation error

- [ ] **Malicious Files**
  - PHP file renamed to .jpg
  - Expected: Validation error or safe handling

- [ ] **Path Traversal**
  - Filename: `../../etc/passwd.jpg`
  - Expected: Sanitized, safe storage

#### Test Command:
```bash
# Create test files
dd if=/dev/zero of=large.jpg bs=1M count=5  # 5MB file
echo "<?php phpinfo(); ?>" > malicious.jpg  # PHP disguised as image
```

---

### 7. Security Testing

#### CSRF Protection
- [ ] **Without Token**
  ```bash
  curl -X POST http://localhost:8000/students \
    -d "student_name=Test"
  ```
  - Expected: 419 error (CSRF token mismatch)

#### SQL Injection
- [ ] **Search Field**
  - Enter: `' OR '1'='1`
  - Expected: No records or safe handling

- [ ] **Filter Parameters**
  - URL: `?year=2025' OR '1'='1`
  - Expected: Safe handling, no SQL error

#### XSS Prevention
- [ ] **Student Name**
  - Enter: `<script>alert('XSS')</script>`
  - Save and view
  - Expected: Escaped HTML, no script execution

- [ ] **Address Fields**
  - Enter: `<img src=x onerror=alert(1)>`
  - Expected: Escaped, no execution

#### Authentication Bypass
- [ ] **Direct URL Access**
  - Logout
  - Try: `http://localhost:8000/students/1/edit`
  - Expected: Redirect to login

- [ ] **Role Escalation**
  - Login as Viewer
  - Try: `http://localhost:8000/students/create`
  - Expected: 403 Forbidden

---

### 8. Performance Testing

#### Load Testing
```bash
# Install Apache Bench
sudo apt install apache2-utils

# Test login page
ab -n 1000 -c 10 http://localhost:8000/login

# Test student list
ab -n 500 -c 10 -H "Cookie: laravel_session=..." http://localhost:8000/students
```

#### Database Query Performance
```sql
-- Check slow queries
SELECT * FROM students WHERE student_name LIKE '%test%';
EXPLAIN SELECT * FROM students WHERE student_birth_certificate_pin = '1234567890';
```

#### Expected Results:
- [ ] Login page: < 200ms response time
- [ ] Student list: < 500ms for 100 students
- [ ] PDF generation: < 3s for single student
- [ ] CSV import: < 30s for 100 students

---

## 🔬 Automated Testing

### Setting Up PHPUnit

#### Install Dependencies
```bash
composer require --dev phpunit/phpunit
composer require --dev laravel/dusk  # For browser testing
```

#### Configure PHPUnit
Create `phpunit.xml`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true">
    <testsuites>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_DATABASE" value="slss_testing"/>
    </php>
</phpunit>
```

---

### Unit Tests

#### Test Student Service
```php
<?php
// tests/Unit/StudentServiceTest.php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\StudentService;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StudentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StudentService();
    }

    /** @test */
    public function it_can_create_student_with_basic_data()
    {
        $data = [
            'student_name' => 'Test Student',
            'form_1_class' => 'A',
            'student_gender' => 'Male'
        ];

        $student = $this->service->createStudent($data);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals('Test Student', $student->student_name);
        $this->assertDatabaseHas('students', ['student_name' => 'Test Student']);
    }

    /** @test */
    public function it_can_create_student_with_photo()
    {
        $photo = UploadedFile::fake()->image('passport.jpg', 150, 150);
        $data = ['student_name' => 'Test Student'];

        $student = $this->service->createStudent($data, $photo);

        $this->assertNotNull($student->student_passport_photo);
        $this->assertStringContainsString('storage/passports/', $student->student_passport_photo);
    }

    /** @test */
    public function it_can_update_student()
    {
        $student = Student::factory()->create(['student_name' => 'Original Name']);

        $updated = $this->service->updateStudent($student, ['student_name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $updated->student_name);
        $this->assertDatabaseHas('students', ['student_name' => 'Updated Name']);
    }

    /** @test */
    public function it_deletes_photo_when_student_deleted()
    {
        $student = Student::factory()->create([
            'student_passport_photo' => 'storage/passports/test.jpg'
        ]);

        // Create fake file
        Storage::fake('public');
        Storage::disk('public')->put('passports/test.jpg', 'fake content');

        $this->service->deleteStudent($student);

        Storage::disk('public')->assertMissing('passports/test.jpg');
    }
}
```

#### Run Unit Tests
```bash
php artisan test --testsuite=Unit
```

---

### Feature Tests

#### Test Student Controller
```php
<?php
// tests/Feature/StudentControllerTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_cannot_access_students()
    {
        $response = $this->get('/students');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_view_students()
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($user)->get('/students');

        $response->assertStatus(200);
        $response->assertViewIs('students.index');
    }

    /** @test */
    public function admin_can_create_student()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $data = [
            'student_name' => 'New Student',
            'form_1_class' => 'A',
            'student_gender' => 'Male'
        ];

        $response = $this->actingAs($admin)->post('/students', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', ['student_name' => 'New Student']);
    }

    /** @test */
    public function viewer_cannot_create_student()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer)->post('/students', [
            'student_name' => 'Test'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/students', []);

        $response->assertSessionHasErrors('student_name');
    }

    /** @test */
    public function it_validates_unique_birth_certificate_pin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Student::factory()->create(['student_birth_certificate_pin' => '1234567890']);

        $response = $this->actingAs($admin)->post('/students', [
            'student_name' => 'Test',
            'student_birth_certificate_pin' => '1234567890'
        ]);

        $response->assertSessionHasErrors('student_birth_certificate_pin');
    }

    /** @test */
    public function it_validates_photo_upload()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($admin)->post('/students', [
            'student_name' => 'Test',
            'student_passport_photo' => $invalidFile
        ]);

        $response->assertSessionHasErrors('student_passport_photo');
    }

    /** @test */
    public function it_can_filter_students_by_year()
    {
        $user = User::factory()->create();
        Student::factory()->create(['registration_date' => '2025-01-01']);
        Student::factory()->create(['registration_date' => '2024-01-01']);

        $response = $this->actingAs($user)->get('/students?year=2025');

        $response->assertStatus(200);
        $response->assertViewHas('students', function ($students) {
            return $students->count() === 1;
        });
    }

    /** @test */
    public function admin_can_delete_student()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();

        $response = $this->actingAs($admin)->delete("/students/{$student->id}");

        $response->assertRedirect('/students');
        $this->assertSoftDeleted('students', ['id' => $student->id]);
    }

    /** @test */
    public function viewer_cannot_delete_student()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $student = Student::factory()->create();

        $response = $this->actingAs($viewer)->delete("/students/{$student->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'deleted_at' => null]);
    }
}
```

#### Run Feature Tests
```bash
php artisan test --testsuite=Feature
```

---

### Browser Testing with Laravel Dusk

#### Setup Dusk
```bash
composer require --dev laravel/dusk
php artisan dusk:install
```

#### Create Browser Test
```php
<?php
// tests/Browser/StudentManagementTest.php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\User;
use Laravel\Dusk\Browser;

class StudentManagementTest extends DuskTestCase
{
    /** @test */
    public function admin_can_create_student_via_browser()
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/students')
                    ->clickLink('Add New Student')
                    ->type('student_name', 'Browser Test Student')
                    ->select('form_1_class', 'A')
                    ->select('student_gender', 'Male')
                    ->press('Save Changes')
                    ->assertPathIs('/students/*')
                    ->assertSee('Browser Test Student');
        });
    }

    /** @test */
    public function user_can_filter_students()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/students')
                    ->select('year', '2025')
                    ->pause(1000)  // Wait for page reload
                    ->assertPathIs('/students')
                    ->assertQueryStringHas('year', '2025');
        });
    }
}
```

#### Run Dusk Tests
```bash
php artisan dusk
```

---

### Test Coverage

#### Generate Coverage Report
```bash
php artisan test --coverage

# Or with HTML report
php artisan test --coverage-html coverage-report
```

#### Coverage Goals
- [ ] Unit Tests: > 80% coverage
- [ ] Feature Tests: > 70% coverage
- [ ] Critical paths: 100% coverage
  - Authentication
  - Student CRUD
  - PDF generation
  - CSV import

---

## 📊 Test Data Factory

Create reusable test data:

```php
<?php
// database/factories/StudentFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_name' => $this->faker->name(),
            'form_1_class' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F']),
            'student_gender' => $this->faker->randomElement(['Male', 'Female']),
            'student_dob' => $this->faker->date('Y-m-d', '-10 years'),
            'student_birth_certificate_pin' => $this->faker->unique()->numerify('##########'),
            'student_current_address' => $this->faker->address(),
            'student_email' => $this->faker->safeEmail(),
            'registration_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'mother_name' => $this->faker->name('female'),
            'father_name' => $this->faker->name('male'),
        ];
    }
}
```

Usage:
```php
// Create single student
$student = Student::factory()->create();

// Create 10 students
$students = Student::factory()->count(10)->create();

// Create with specific data
$student = Student::factory()->create([
    'student_name' => 'Specific Name',
    'form_1_class' => 'A'
]);
```

---

## 🎯 Quality Assurance Checklist

### Before Release
- [ ] All unit tests passing
- [ ] All feature tests passing
- [ ] Browser tests passing
- [ ] Manual test checklist completed
- [ ] Security tests passed
- [ ] Performance benchmarks met
- [ ] No console errors in browser
- [ ] No PHP warnings/errors in logs
- [ ] All validation working
- [ ] All features accessible by correct roles
- [ ] PDF generation working correctly
- [ ] CSV import handling errors gracefully
- [ ] Photo uploads secure and working
- [ ] Database properly indexed
- [ ] Caching working correctly

---

## 📝 Bug Reporting Template

When reporting bugs, include:

```markdown
**Bug Description:**
[Clear description of the issue]

**Steps to Reproduce:**
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected Behavior:**
[What should happen]

**Actual Behavior:**
[What actually happens]

**Environment:**
- Browser: [e.g., Chrome 120]
- OS: [e.g., macOS 14]
- App Version: [e.g., 1.0.0]

**Screenshots:**
[If applicable]

**Error Logs:**
```
[Paste relevant logs from storage/logs/laravel.log]
```

**Additional Context:**
[Any other relevant information]
```

---

## 🔄 Continuous Integration (Optional)

### GitHub Actions Example

`.github/workflows/tests.yml`:
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: slss_testing
          MYSQL_ROOT_PASSWORD: password
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.1
          extensions: mbstring, xml, bcmath, gd, mysql

      - name: Install Dependencies
        run: composer install --prefer-dist --no-interaction

      - name: Copy .env
        run: cp .env.example .env

      - name: Generate Key
        run: php artisan key:generate

      - name: Run Tests
        env:
          DB_CONNECTION: mysql
          DB_DATABASE: slss_testing
          DB_USERNAME: root
          DB_PASSWORD: password
        run: php artisan test --coverage
```

---

## 📚 Additional Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Manual](https://phpunit.de/manual/current/en/index.html)
- [Laravel Dusk Documentation](https://laravel.com/docs/dusk)
- [Pest PHP (Alternative Testing Framework)](https://pestphp.com/)

---

**Your SLSS Student Management System is thoroughly testable and ready for quality assurance!** ✅
